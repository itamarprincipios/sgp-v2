<?php

namespace App\Services;

/**
 * Biblioteca do DCRR (Documento Curricular de Roraima) — Etapa B da Fase 2.
 * O DCRR completo (586 páginas) foi fatiado em arquivos por componente
 * curricular e ano em resources/dcrr/ (gerados a partir do PDF oficial).
 * Este serviço seleciona o trecho relevante para o planejamento em análise,
 * a partir da disciplina e da turma, e o entrega ao PlanAnalyzer.
 */
class DcrrLibrary
{
    /** Componentes com um arquivo por ano (1º-9º). */
    private const PER_YEAR = [
        'lingua_portuguesa', 'matematica', 'ciencias',
        'geografia', 'historia', 'ensino_religioso',
    ];

    /** Componentes do professor polivalente/titular dos anos iniciais (por ano). */
    private const POLIVALENTE = [
        'lingua_portuguesa', 'matematica', 'ciencias', 'historia', 'geografia',
        'ensino_religioso',
    ];

    /**
     * Retorna o material do DCRR relevante para a disciplina/turma, ou null
     * quando não há como determinar a seção (o PlanAnalyzer então instrui a
     * IANNE a declarar "não verificado" em vez de inventar).
     */
    public function excerptFor(?string $discipline, ?string $className, int $maxChars = 150000): ?string
    {
        $ano = $this->yearFromClassName($className);
        $slugs = $this->resolveSlugs($this->normalize($discipline ?? ''), $ano, $className);

        if (empty($slugs)) {
            return null;
        }

        // Carrega as seções inteiras; só trunca (proporcionalmente) se o total
        // estourar $maxChars. Truncar demais faz a IA afirmar que habilidades
        // "não existem no DCRR" quando existem — erro inaceitável no parecer.
        $contents = [];
        $total = 0;
        foreach ($slugs as $slug) {
            $path = resource_path("dcrr/{$slug}.txt");
            if (!is_file($path)) {
                continue;
            }
            $content = file_get_contents($path);
            $contents[] = $content;
            $total += mb_strlen($content);
        }

        if (empty($contents)) {
            return null;
        }

        if ($total > $maxChars) {
            foreach ($contents as $i => $content) {
                $allowed = (int) floor(mb_strlen($content) / $total * $maxChars);
                if (mb_strlen($content) > $allowed) {
                    $contents[$i] = mb_substr($content, 0, $allowed)
                        . "\n[ATENÇÃO: seção truncada — o DCRR completo contém MAIS habilidades e orientações além das mostradas acima]";
                }
            }
        }

        return implode("\n\n---\n\n", $contents);
    }

    /**
     * Extrai o ano (1-9) do nome da turma (ex: "3º Ano B" → 3).
     */
    private function yearFromClassName(?string $className): ?int
    {
        if (!$className) {
            return null;
        }
        if (preg_match('/(\d)\s*[ºo°ª]?\s*ano/iu', $className, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/^\s*(\d)\b/', $className, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * @return string[] slugs de arquivos em resources/dcrr/
     */
    private function resolveSlugs(string $discipline, ?int $ano, ?string $className): array
    {
        $class = $this->normalize($className ?? '');

        // Educação infantil: pela disciplina ou pelo nome da turma
        $infantilTokens = ['infantil', 'pre', 'maternal', 'bercario', 'creche'];
        foreach ($infantilTokens as $token) {
            if (str_contains($discipline, $token) || str_contains($class, $token)) {
                return ['educacao_infantil'];
            }
        }

        // Componentes que não dependem do ano exato
        if (str_contains($discipline, 'arte')) {
            return [($ano === null || $ano <= 5) ? 'arte_iniciais' : 'arte_finais'];
        }
        if (str_contains($discipline, 'fisica') || str_contains($discipline, 'edfis')) {
            return [match (true) {
                $ano === null, $ano <= 2 => 'educacao_fisica_1_2',
                $ano <= 5 => 'educacao_fisica_3_5',
                $ano <= 7 => 'educacao_fisica_6_7',
                default => 'educacao_fisica_8_9',
            }];
        }

        // Daqui em diante é preciso saber o ano
        if ($ano === null) {
            return [];
        }

        if (str_contains($discipline, 'ingles')) {
            return $ano >= 6 ? ["lingua_inglesa_{$ano}"] : [];
        }
        if (str_contains($discipline, 'espanhol')) {
            return $ano >= 6 ? ["lingua_espanhola_{$ano}"] : [];
        }

        $map = [
            'portugu' => 'lingua_portuguesa',
            'matemat' => 'matematica',
            'cienc' => 'ciencias',
            'geograf' => 'geografia',
            'histor' => 'historia',
            'religios' => 'ensino_religioso',
        ];
        foreach ($map as $token => $slug) {
            if (str_contains($discipline, $token)) {
                return ["{$slug}_{$ano}"];
            }
        }

        // Polivalente (ou disciplina vazia/não mapeada) nos anos iniciais:
        // todos os componentes do titular, incluindo Arte (arquivo único 1º-5º)
        if ($ano <= 5 && ($discipline === '' || str_contains($discipline, 'polivalente') || str_contains($discipline, 'todas'))) {
            $slugs = array_map(fn ($c) => "{$c}_{$ano}", self::POLIVALENTE);
            $slugs[] = 'arte_iniciais';
            return $slugs;
        }

        return [];
    }

    /**
     * Minúsculas sem acentos, para matching tolerante.
     */
    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $accents = [
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i',
            'ó'=>'o','õ'=>'o','ô'=>'o','ú'=>'u','ü'=>'u','ç'=>'c',
        ];
        return strtr($text, $accents);
    }
}
