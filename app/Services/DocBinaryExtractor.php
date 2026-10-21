<?php

namespace App\Services;

use Exception;

/**
 * Extrai texto de .doc (Word 97-2003), o formato binário OLE2.
 *
 * Por que escrito à mão: .doc não é ZIP (ao contrário do .docx), a Hostinger é
 * shared hosting sem antiword/libreoffice, e o `vendor/` não vai no Git — uma
 * dependência de composer exigiria upload manual a cada deploy. A mesma razão
 * pela qual o AIService fala HTTP direto com a API em vez de usar SDK.
 *
 * Caminho principal: lê o container OLE2 (FAT, diretório, mini-stream), acha os
 * streams "WordDocument" e "<n>Table", e percorre a piece table do FIB — que é
 * como o Word 97 guarda o texto, em pedaços que podem estar em CP1252 de 1 byte
 * ou em UTF-16LE de 2 bytes, misturados no mesmo arquivo.
 *
 * Caminho de recuo: se a estrutura não bater (arquivo truncado, variante
 * estranha), varre os bytes atrás de trechos legíveis. Pior qualidade, mas
 * entrega algo em vez de derrubar o upload.
 */
class DocBinaryExtractor
{
    private const ASSINATURA_OLE2 = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";

    private string $dados;
    private int $tamSetor;
    private int $tamMiniSetor;
    private int $corteMiniStream;

    /** @var int[] */
    private array $fat = [];
    /** @var int[] */
    private array $miniFat = [];
    private string $miniStream = '';

    public function extract(string $filePath): string
    {
        // Zerar é obrigatório: o serviço pode ser reaproveitado entre arquivos
        // (upload em lote, container do Laravel), e a FAT de um documento
        // anterior faria a leitura do seguinte apontar para setores errados.
        $this->fat = [];
        $this->miniFat = [];
        $this->miniStream = '';

        $this->dados = (string) file_get_contents($filePath);

        if (strlen($this->dados) < 512 || substr($this->dados, 0, 8) !== self::ASSINATURA_OLE2) {
            throw new Exception('Arquivo .doc inválido (assinatura OLE2 ausente).');
        }

        try {
            $texto = $this->extrairViaPieceTable();
        } catch (Exception $e) {
            $texto = $this->extrairPorVarredura();

            if (trim($texto) === '') {
                throw $e;
            }
        }

        return $this->normalizar($texto);
    }

    // ------------------------------------------------------------------
    // Caminho principal
    // ------------------------------------------------------------------

    private function extrairViaPieceTable(): string
    {
        $this->lerCabecalho();
        $diretorio = $this->lerDiretorio();

        $wordDocument = $this->lerStreamPorNome($diretorio, 'WordDocument');
        if ($wordDocument === null || strlen($wordDocument) < 0x01AA) {
            throw new Exception('Stream WordDocument ausente ou curto demais.');
        }

        if ($this->u16($wordDocument, 0x00) !== 0xA5EC) {
            throw new Exception('FIB não reconhecido (wIdent inesperado).');
        }

        // Bit 9 das flags diz em qual dos dois streams de tabela olhar.
        $flags = $this->u16($wordDocument, 0x0A);
        $nomeTabela = ($flags & 0x0200) ? '1Table' : '0Table';

        $tabela = $this->lerStreamPorNome($diretorio, $nomeTabela)
            ?? $this->lerStreamPorNome($diretorio, $nomeTabela === '1Table' ? '0Table' : '1Table');

        if ($tabela === null) {
            throw new Exception('Stream de tabela ausente.');
        }

        $fcClx = $this->u32($wordDocument, 0x01A2);
        $lcbClx = $this->u32($wordDocument, 0x01A6);

        if ($lcbClx <= 0 || $fcClx + $lcbClx > strlen($tabela)) {
            throw new Exception('Piece table fora dos limites do stream de tabela.');
        }

        $clx = substr($tabela, $fcClx, $lcbClx);
        $plcPcd = $this->acharPlcPcd($clx);

        return $this->montarTexto($wordDocument, $plcPcd);
    }

    /**
     * O Clx é uma sequência de Prc (clxt=1, propriedades que não interessam)
     * terminada por um Pcdt (clxt=2), que carrega a piece table.
     */
    private function acharPlcPcd(string $clx): string
    {
        $i = 0;
        $n = strlen($clx);

        while ($i < $n) {
            $clxt = ord($clx[$i]);

            if ($clxt === 0x01) {
                if ($i + 3 > $n) {
                    break;
                }
                $cb = $this->u16($clx, $i + 1);
                $i += 3 + $cb;
                continue;
            }

            if ($clxt === 0x02) {
                if ($i + 5 > $n) {
                    break;
                }
                $lcb = $this->u32($clx, $i + 1);

                return substr($clx, $i + 5, $lcb);
            }

            break;
        }

        throw new Exception('Pcdt não encontrado no Clx.');
    }

    /**
     * PlcPcd = (n+1) CPs de 4 bytes, seguidos de n PCDs de 8 bytes. Cada PCD
     * aponta para um trecho do WordDocument e diz se ele está comprimido
     * (CP1252, 1 byte por caractere) ou em UTF-16LE.
     */
    private function montarTexto(string $wordDocument, string $plcPcd): string
    {
        $n = intdiv(strlen($plcPcd) - 4, 12);
        if ($n <= 0) {
            throw new Exception('Piece table vazia.');
        }

        $inicioPcds = ($n + 1) * 4;
        $partes = [];

        for ($i = 0; $i < $n; $i++) {
            $cpInicio = $this->u32($plcPcd, $i * 4);
            $cpFim = $this->u32($plcPcd, ($i + 1) * 4);
            $qtdChars = $cpFim - $cpInicio;

            if ($qtdChars <= 0) {
                continue;
            }

            $fc = $this->u32($plcPcd, $inicioPcds + $i * 8 + 2);
            $comprimido = ($fc & 0x40000000) !== 0;
            $offset = $fc & 0x3FFFFFFF;

            if ($comprimido) {
                $bruto = substr($wordDocument, intdiv($offset, 2), $qtdChars);
                $partes[] = mb_convert_encoding($bruto, 'UTF-8', 'Windows-1252');
            } else {
                $bruto = substr($wordDocument, $offset, $qtdChars * 2);
                $partes[] = mb_convert_encoding($bruto, 'UTF-8', 'UTF-16LE');
            }
        }

        if (empty($partes)) {
            throw new Exception('Nenhum trecho de texto recuperado.');
        }

        return implode('', $partes);
    }

    // ------------------------------------------------------------------
    // Container OLE2
    // ------------------------------------------------------------------

    private function lerCabecalho(): void
    {
        $this->tamSetor = 1 << $this->u16($this->dados, 0x1E);
        $this->tamMiniSetor = 1 << $this->u16($this->dados, 0x20);
        $this->corteMiniStream = $this->u32($this->dados, 0x38) ?: 4096;

        if ($this->tamSetor < 128 || $this->tamSetor > 65536) {
            throw new Exception('Tamanho de setor OLE2 implausível.');
        }

        // DIFAT: 109 entradas no cabeçalho, o resto encadeado em setores.
        $setoresFat = [];
        for ($i = 0; $i < 109; $i++) {
            $s = $this->u32($this->dados, 0x4C + $i * 4);
            if ($s === 0xFFFFFFFF) {
                break;
            }
            $setoresFat[] = $s;
        }

        $setorDifat = $this->u32($this->dados, 0x44);
        $guarda = 0;
        while ($setorDifat !== 0xFFFFFFFF && $setorDifat !== 0xFFFFFFFE && $guarda++ < 1000) {
            $bloco = $this->setor($setorDifat);
            $porBloco = intdiv($this->tamSetor, 4) - 1;

            for ($i = 0; $i < $porBloco; $i++) {
                $s = $this->u32($bloco, $i * 4);
                if ($s === 0xFFFFFFFF) {
                    break;
                }
                $setoresFat[] = $s;
            }

            $setorDifat = $this->u32($bloco, $porBloco * 4);
        }

        foreach ($setoresFat as $s) {
            $bloco = $this->setor($s);
            for ($i = 0, $max = intdiv($this->tamSetor, 4); $i < $max; $i++) {
                $this->fat[] = $this->u32($bloco, $i * 4);
            }
        }

        if (empty($this->fat)) {
            throw new Exception('FAT vazia.');
        }
    }

    /**
     * @return array<int, array{nome: string, setor: int, tamanho: int}>
     */
    private function lerDiretorio(): array
    {
        $bruto = $this->cadeia($this->u32($this->dados, 0x30));
        $entradas = [];

        for ($off = 0; $off + 128 <= strlen($bruto); $off += 128) {
            $tamNome = $this->u16($bruto, $off + 0x40);
            if ($tamNome < 2) {
                continue;
            }

            $nome = mb_convert_encoding(substr($bruto, $off, $tamNome - 2), 'UTF-8', 'UTF-16LE');

            $entradas[] = [
                'nome' => $nome,
                'tipo' => ord($bruto[$off + 0x42]),
                'setor' => $this->u32($bruto, $off + 0x74),
                'tamanho' => $this->u32($bruto, $off + 0x78),
            ];
        }

        if (empty($entradas)) {
            throw new Exception('Diretório OLE2 vazio.');
        }

        // A entrada raiz (tipo 5) guarda o mini-stream, onde ficam os streams
        // menores que 4096 bytes — o 1Table costuma estar lá.
        foreach ($entradas as $e) {
            if ($e['tipo'] === 5 && $e['tamanho'] > 0) {
                $this->miniStream = substr($this->cadeia($e['setor']), 0, $e['tamanho']);
                break;
            }
        }

        $primeiroMiniFat = $this->u32($this->dados, 0x3C);
        if ($primeiroMiniFat !== 0xFFFFFFFF && $primeiroMiniFat !== 0xFFFFFFFE) {
            $bloco = $this->cadeia($primeiroMiniFat);
            for ($i = 0, $max = intdiv(strlen($bloco), 4); $i < $max; $i++) {
                $this->miniFat[] = $this->u32($bloco, $i * 4);
            }
        }

        return $entradas;
    }

    /**
     * @param array<int, array{nome: string, setor: int, tamanho: int}> $diretorio
     */
    private function lerStreamPorNome(array $diretorio, string $nome): ?string
    {
        foreach ($diretorio as $e) {
            if ($e['nome'] !== $nome || $e['tamanho'] <= 0) {
                continue;
            }

            $bruto = $e['tamanho'] < $this->corteMiniStream && $this->miniStream !== ''
                ? $this->cadeiaMini($e['setor'])
                : $this->cadeia($e['setor']);

            return substr($bruto, 0, $e['tamanho']);
        }

        return null;
    }

    private function setor(int $n): string
    {
        $inicio = 512 + $n * $this->tamSetor;
        $bloco = substr($this->dados, $inicio, $this->tamSetor);

        return str_pad($bloco === false ? '' : $bloco, $this->tamSetor, "\0");
    }

    private function cadeia(int $setor): string
    {
        $saida = '';
        $visitados = 0;
        $limite = count($this->fat) + 16;

        while ($setor !== 0xFFFFFFFE && $setor !== 0xFFFFFFFF && $visitados++ < $limite) {
            if (!isset($this->fat[$setor])) {
                break;
            }
            $saida .= $this->setor($setor);
            $setor = $this->fat[$setor];
        }

        return $saida;
    }

    private function cadeiaMini(int $setor): string
    {
        $saida = '';
        $visitados = 0;
        $limite = count($this->miniFat) + 16;

        while ($setor !== 0xFFFFFFFE && $setor !== 0xFFFFFFFF && $visitados++ < $limite) {
            if (!isset($this->miniFat[$setor])) {
                break;
            }
            $saida .= substr($this->miniStream, $setor * $this->tamMiniSetor, $this->tamMiniSetor);
            $setor = $this->miniFat[$setor];
        }

        return $saida;
    }

    // ------------------------------------------------------------------
    // Recuo e limpeza
    // ------------------------------------------------------------------

    /**
     * Varredura burra: junta trechos legíveis em UTF-16LE e em CP1252. Só roda
     * quando a leitura estruturada falha; preserva acentuação (o truque antigo
     * de regex ASCII devolvia "avaliao" no lugar de "avaliação").
     */
    private function extrairPorVarredura(): string
    {
        $utf16 = '';
        if (preg_match_all('/(?:[\x20-\x7E\xC0-\xFF]\x00){12,}/', $this->dados, $m)) {
            foreach ($m[0] as $trecho) {
                $utf16 .= mb_convert_encoding($trecho, 'UTF-8', 'UTF-16LE') . "\n";
            }
        }

        if (mb_strlen($utf16) > 200) {
            return $utf16;
        }

        $ansi = '';
        if (preg_match_all('/[\x20-\x7E\xC0-\xFF\x0D\x0A\x09]{20,}/', $this->dados, $m)) {
            foreach ($m[0] as $trecho) {
                $ansi .= mb_convert_encoding($trecho, 'UTF-8', 'Windows-1252') . "\n";
            }
        }

        return mb_strlen($ansi) > mb_strlen($utf16) ? $ansi : $utf16;
    }

    /**
     * Converte os caracteres de controle do Word em estrutura legível — a mesma
     * convenção do extrator de .docx, porque planejamento é tabela e a IANNE
     * precisa saber qual célula pertence a qual dia.
     */
    private function normalizar(string $texto): string
    {
        // Instrução de campo (entre 0x13 e 0x14) é lixo tipo "HYPERLINK \l ...".
        $texto = preg_replace('/\x13[^\x14\x15]*[\x14\x15]?/u', '', $texto) ?? $texto;

        $texto = strtr($texto, [
            "\x07" => ' | ',   // fim de célula / linha de tabela
            "\x0D" => "\n",    // fim de parágrafo
            "\x0B" => "\n",    // quebra de linha
            "\x0C" => "\n",    // quebra de página
            "\x1E" => '-',     // hífen não separável
            "\x1F" => '',      // hífen opcional
            "\xC2\xA0" => ' ', // espaço não separável já em UTF-8
            "\x01" => '',      // imagem embutida
            "\x08" => '',
            "\x14" => '',
            "\x15" => '',
            "\x05" => '',      // marca de comentário
        ]);

        $texto = preg_replace('/[\x00-\x08\x0E-\x1F]/u', '', $texto) ?? $texto;

        $texto = preg_replace('/[ \t]+/u', ' ', $texto) ?? $texto;
        $texto = preg_replace('/\s*\n\s*\|/u', ' |', $texto) ?? $texto;
        $texto = preg_replace('/\|\s*\n/u', "|\n", $texto) ?? $texto;
        $texto = preg_replace('/ *\n *(\| *\n *)+/u', "\n", $texto) ?? $texto;
        $texto = preg_replace('/^[ |]+$/mu', '', $texto) ?? $texto;
        $texto = preg_replace('/\n{3,}/u', "\n\n", $texto) ?? $texto;

        return trim($texto);
    }

    private function u16(string $s, int $off): int
    {
        if ($off + 2 > strlen($s)) {
            return 0;
        }

        return unpack('v', substr($s, $off, 2))[1];
    }

    private function u32(string $s, int $off): int
    {
        if ($off + 4 > strlen($s)) {
            return 0xFFFFFFFF;
        }

        return unpack('V', substr($s, $off, 4))[1];
    }
}
