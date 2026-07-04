<?php

namespace App\Services;

use App\Models\Document;

/**
 * Fase 2 do pivô — correção assistida. Monta a rubrica de avaliação e pede à
 * IANNE um parecer honesto sobre o planejamento. Não inventa DCRR/BNCC: quando
 * falta material de referência, instrui o modelo a declarar "não verificado".
 */
class PlanAnalyzer
{
    private AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * @param array $context vigencia, aulas, observacoes, dcrr (opcional — Etapa B)
     */
    public function analyze(Document $document, array $context = []): string
    {
        $plan = mb_substr($document->content_text ?? '', 0, 12000);
        $professor = $document->user->name ?? 'não identificado';
        $turma = $document->schoolClass->name ?? 'não informada';
        $disciplina = $document->discipline ?? 'não informada';
        $vigencia = trim($context['vigencia'] ?? '') ?: ($document->reference_label ?? 'não informado');
        $aulas = trim($context['aulas'] ?? '') ?: 'não informado';
        $observacoes = trim($context['observacoes'] ?? '') ?: 'nenhuma';
        $dcrr = trim($context['dcrr'] ?? '');

        $dcrrBloco = $dcrr !== ''
            ? "MATERIAL DE REFERÊNCIA DO DCRR (currículo de Roraima) para este componente/etapa:\n{$dcrr}\n"
            : "MATERIAL DO DCRR: NÃO fornecido. No item de alinhamento com o DCRR, escreva exatamente que não foi possível verificar por falta do documento de referência — NÃO invente habilidades, códigos ou alinhamentos do DCRR.\n";

        $prompt = <<<PROMPT
Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima avaliando um planejamento de ensino. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do plano e no material de referência fornecido. NÃO invente habilidades, códigos da BNCC nem alinhamentos que não estejam comprovados; quando algo não constar no plano, escreva "não consta".

DADOS INFORMADOS PELO COORDENADOR:
- Professor(a): {$professor}
- Turma/ano: {$turma}
- Componente curricular: {$disciplina}
- Período de vigência informado: {$vigencia}
- Nº de aulas previstas: {$aulas}
- Observações do coordenador: {$observacoes}

{$dcrrBloco}
TEXTO DO PLANEJAMENTO:
{$plan}

Produza um PARECER em português, em markdown, avaliando cada item abaixo. Em cada item, diga o que está adequado e o que precisa melhorar, citando trechos do plano quando útil:

1. **Identificação** — o cabeçalho está completo (escola, professor, ano/turma, componente, período, carga horária)?
2. **Vigência e carga horária** — as datas informadas são coerentes com o bimestre e com o nº de aulas previstas?
3. **Habilidades BNCC** — os códigos citados existem e são adequados ao ano e ao componente? Liste as habilidades que identificar.
4. **Objetos de conhecimento** — os conteúdos correspondem às habilidades declaradas?
5. **Alinhamento com o DCRR** — conforme a regra do material de referência acima.
6. **Objetivos de aprendizagem** — estão claros e mensuráveis, coerentes com as habilidades?
7. **Metodologias** — estão descritas, são variadas e adequadas à faixa etária?
8. **Avaliação** — há instrumentos e critérios definidos? Contempla avaliação formativa?
9. **Inclusão e adaptações** — considera diversidade e alunos com deficiência?
10. **Coerência geral** — objetivos, habilidades, conteúdos, metodologia e avaliação conversam entre si?

Ao final, escreva uma seção "## Parecer geral" com: (a) pontos fortes; (b) o que precisa ser ajustado antes de aprovar; (c) a situação geral em uma frase.
PROMPT;

        // maxTokens alto (o parecer é longo) e timeout generoso.
        return $this->ai->query($prompt, false, 4096, 120);
    }
}
