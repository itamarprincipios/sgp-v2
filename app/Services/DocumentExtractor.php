<?php

namespace App\Services;

use App\Models\Document;
use DOMDocument;
use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Log;

class DocumentExtractor
{
    /**
     * Extrai texto de um arquivo .docx.
     *
     * @param string $filePath Caminho completo do arquivo
     * @return string Texto extraído
     * @throws Exception Se não conseguir ler o arquivo
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado: $filePath");
        }
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'docx') {
            return $this->extractFromDocx($filePath);
        } elseif ($extension === 'pdf') {
            return $this->extractFromPdf($filePath);
        } elseif ($extension === 'doc') {
            return "[Arquivo .doc antigo - Por favor, converta para .docx para extração de conteúdo]";
        } else {
            throw new Exception("Formato não suportado: $extension");
        }
    }

    /**
     * Extrai texto de PDF via Gemini multimodal (inline base64).
     * O shared hosting não permite instalar libs de parsing de PDF,
     * então a própria IA transcreve o conteúdo (funciona inclusive com escaneados).
     *
     * @param string $filePath Caminho do arquivo .pdf
     * @return string Texto extraído
     */
    private function extractFromPdf(string $filePath): string
    {
        $ai = app(AIService::class);

        // Tabelas viram linhas com " | ", igual ao que o extrator de .docx faz —
        // é o que permite à IANNE saber qual célula pertence a qual dia.
        $prompt = "Transcreva TODO o texto deste documento PDF, na ordem em que aparece, "
            . "incluindo cabeçalhos, tabelas e rodapés. Em tabelas, separe as células com \" | \" "
            . "e cada linha da tabela em uma linha do texto. "
            . "Não resuma, não comente, não adicione nada — retorne apenas o texto transcrito.";

        try {
            $texto = $ai->queryWithFile($prompt, $filePath, 'application/pdf');
        } catch (Exception $e) {
            // Modelo que não aceita o teto alto devolve erro citando max_tokens.
            // Repete com o valor conservador em vez de derrubar o upload.
            if (stripos($e->getMessage(), 'max_tokens') === false) {
                throw $e;
            }
            Log::warning('Transcrição de PDF: teto rejeitado pelo modelo, repetindo com 8192. ' . $e->getMessage());
            $texto = $ai->queryWithFile($prompt, $filePath, 'application/pdf', 8192);
        }

        $texto = trim($texto);

        // stop_reason = max_tokens significa transcrição interrompida no meio.
        // Sem este aviso, meio documento seria analisado como se fosse o todo —
        // e a IANNE apontaria como ausente o que ela nunca chegou a ler.
        if ($ai->lastStopReason() === 'max_tokens') {
            $texto .= "\n\n[ATENÇÃO: a transcrição deste PDF foi INTERROMPIDA por limite de tamanho e está incompleta. "
                . "NÃO afirme que algo está ausente com base no que não aparece acima — o documento continua além deste ponto. "
                . "Recomende ao coordenador reenviar o planejamento em .docx.]";
        }

        return $texto;
    }
    
    /**
     * Extrai texto de arquivo .docx usando ZipArchive.
     *
     * @param string $filePath Caminho do arquivo .docx
     * @return string Texto extraído
     */
    private function extractFromDocx(string $filePath): string
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filePath) !== true) {
            throw new Exception("Não foi possível abrir o arquivo como ZIP");
        }
        
        $content = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($content === false) {
            throw new Exception("Não foi possível encontrar document.xml no arquivo");
        }
        
        return $this->extractTextFromXml($content);
    }
    
    /**
     * Extrai texto puro do XML do Word.
     *
     * @param string $xml Conteúdo XML
     * @return string Texto extraído
     */
    private function extractTextFromXml(string $xml): string
    {
        // Planejamento quase sempre é TABELA (dia | conteúdo | habilidade |
        // metodologia | avaliação). Sem preservar a estrutura, tudo virava uma
        // linha única e a IANNE não conseguia dizer qual célula pertencia a
        // qual dia — daí concluir que "falta avaliação" onde ela existe.
        // Injetamos marcadores como nós <w:t> para eles saírem na ordem certa.
        $xml = preg_replace('/<w:tab\b[^>]*\/?>/', '<w:t>[[TAB]]</w:t>', $xml);
        $xml = preg_replace('/<w:br\b[^>]*\/?>/', '<w:t>[[BR]]</w:t>', $xml);
        $xml = str_replace('</w:tc>', '<w:t>[[CELL]]</w:t></w:tc>', $xml);
        $xml = str_replace('</w:tr>', '<w:t>[[ROW]]</w:t></w:tr>', $xml);
        $xml = str_replace('</w:p>', '<w:t>[[PAR]]</w:t></w:p>', $xml);

        $xml = str_replace('w:', '', $xml);

        $dom = new DOMDocument();
        @$dom->loadXML($xml);

        $textNodes = $dom->getElementsByTagName('t');

        $text = '';
        foreach ($textNodes as $node) {
            $text .= $node->nodeValue;
        }

        $text = str_replace(
            ['[[TAB]]', '[[BR]]', '[[PAR]]', '[[CELL]]', '[[ROW]]'],
            [' ',       "\n",     "\n",      ' | ',      "\n"],
            $text
        );

        // Espaços em excesso somem; quebras de linha ficam.
        $text = preg_replace('/[ \t]+/', ' ', $text);
        // Parágrafo dentro de célula deixava " \n | " antes do separador.
        $text = preg_replace('/\s*\n\s*\|/', ' |', $text);
        $text = preg_replace('/\|\s*\n/', "|\n", $text);
        $text = preg_replace('/ *\n *(\| *\n *)+/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/^[ |]+$/m', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
    
    /**
     * Extrai e salva conteúdo de um documento.
     *
     * @param int $documentId ID do documento no banco
     * @return array Resultado com sucesso e mensagem
     */
    public function extractAndSave(int $documentId): array
    {
        try {
            $document = Document::findOrFail($documentId);
            $filePath = $document->file_path;
            
            // Determinar o caminho completo com base em prováveis localizações
            if (file_exists($filePath)) {
                $fullPath = $filePath;
            } elseif (file_exists(public_path($filePath))) {
                $fullPath = public_path($filePath);
            } elseif (file_exists(public_path('uploads/' . $filePath))) {
                $fullPath = public_path('uploads/' . $filePath);
            } elseif (file_exists(storage_path('app/' . $filePath))) {
                $fullPath = storage_path('app/' . $filePath);
            } elseif (file_exists(storage_path('app/public/' . $filePath))) {
                $fullPath = storage_path('app/public/' . $filePath);
            } else {
                $fullPath = base_path($filePath);
                if (!file_exists($fullPath)) {
                    Log::error("DocumentExtractor: Arquivo não encontrado: $filePath");
                    return [
                        'success' => false,
                        'message' => "Arquivo não encontrado no servidor."
                    ];
                }
            }
            
            $text = $this->extractText($fullPath);
            
            $document->update([
                'content_text' => $text,
                'content_extracted_at' => now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Conteúdo extraído com sucesso',
                'text_length' => strlen($text),
                'preview' => mb_substr($text, 0, 200) . '...'
            ];
            
        } catch (Exception $e) {
            Log::error("DocumentExtractor::extractAndSave error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
}
