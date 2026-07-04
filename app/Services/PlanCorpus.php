<?php

namespace App\Services;

use App\Models\Document;

/**
 * Monta o corpus de planejamentos de uma ou mais escolas para perguntas
 * amplas à IANNE ("quais professores usaram metodologia lúdica?", "erros
 * mais comuns nos planejamentos" etc.) — Fase 3 do pivô.
 * Inclui o texto extraído E o parecer salvo de cada documento.
 */
class PlanCorpus
{
    private const PER_DOC_CONTENT = 2200;
    private const PER_DOC_ANALYSIS = 900;

    /**
     * @return array{text: string, included: int, total: int}
     */
    public function build(array $schoolIds, int $maxChars = 280000): array
    {
        $documents = Document::whereIn('school_id', $schoolIds)
            ->whereIn('status', ['em_correcao', 'corrigido', 'aprovado'])
            ->whereNotNull('content_text')
            ->with(['user:id,name', 'schoolClass:id,name'])
            ->orderByDesc('id')
            ->get();

        $total = $documents->count();
        $blocks = [];
        $used = 0;
        $included = 0;

        foreach ($documents as $i => $doc) {
            $content = mb_substr(trim($doc->content_text), 0, self::PER_DOC_CONTENT);
            if (mb_strlen($doc->content_text) > self::PER_DOC_CONTENT) {
                $content .= ' [...]';
            }

            $block = '### DOCUMENTO ' . ($i + 1) . ": {$doc->title}\n"
                . 'Professor(a): ' . ($doc->user->name ?? 'não identificado')
                . ' | Turma: ' . ($doc->schoolClass->name ?? 'não informada')
                . ' | Disciplina: ' . ($doc->discipline ?? 'não informada')
                . ' | Período: ' . ($doc->reference_label ?? 'não informado')
                . ' | Status: ' . $doc->status . "\n"
                . "CONTEÚDO DO PLANO:\n{$content}\n";

            if (!empty($doc->analysis)) {
                $analysis = mb_substr(trim($doc->analysis), 0, self::PER_DOC_ANALYSIS);
                if (mb_strlen($doc->analysis) > self::PER_DOC_ANALYSIS) {
                    $analysis .= ' [...]';
                }
                $block .= "PARECER DA CORREÇÃO:\n{$analysis}\n";
            }

            $len = mb_strlen($block);
            if ($used + $len > $maxChars) {
                break;
            }

            $blocks[] = $block;
            $used += $len;
            $included++;
        }

        return [
            'text' => implode("\n", $blocks),
            'included' => $included,
            'total' => $total,
        ];
    }
}
