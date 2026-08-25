<?php

namespace App\Services;

use App\Models\TenantReferenceFile;

/**
 * Junta o material de referência que a rede enviou (SuperAdmin → município →
 * Prompts da IANNE) num único bloco para o PlanAnalyzer.
 *
 * Complementa o DcrrLibrary, não o substitui: o parecer recebe o DCRR do
 * componente/ano E as exigências próprias da rede, em seções separadas, para a
 * IANNE saber de onde veio cada cobrança que faz.
 */
class ReferenceLibrary
{
    /**
     * Texto consolidado dos arquivos ativos do município, ou null quando não há
     * nenhum com texto aproveitável.
     *
     * Trunca proporcionalmente se estourar o teto — e avisa dentro do próprio
     * texto, para a IANNE nunca afirmar que uma exigência "não existe" quando
     * ela pode estar na parte cortada.
     */
    public function textFor(?int $tenantId, int $maxChars = 60000): ?string
    {
        if ($tenantId === null) {
            return null;
        }

        $arquivos = TenantReferenceFile::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('content_text')
            ->orderBy('id')
            ->get(['title', 'content_text']);

        $secoes = [];
        $total = 0;

        foreach ($arquivos as $arquivo) {
            $texto = trim((string) $arquivo->content_text);
            if ($texto === '') {
                continue;
            }
            $secoes[] = ['titulo' => $arquivo->title, 'texto' => $texto];
            $total += mb_strlen($texto);
        }

        if (empty($secoes)) {
            return null;
        }

        if ($total > $maxChars) {
            foreach ($secoes as $i => $secao) {
                $permitido = (int) floor(mb_strlen($secao['texto']) / $total * $maxChars);
                if (mb_strlen($secao['texto']) > $permitido) {
                    $secoes[$i]['texto'] = mb_substr($secao['texto'], 0, $permitido)
                        . "\n[ATENÇÃO: documento truncado — há mais conteúdo além do mostrado acima]";
                }
            }
        }

        $partes = array_map(
            fn ($s) => "### {$s['titulo']}\n{$s['texto']}",
            $secoes
        );

        return implode("\n\n", $partes);
    }
}
