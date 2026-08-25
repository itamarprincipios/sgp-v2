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
    /**
     * Teto do texto do plano enviado à IANNE. Margem de segurança deliberada:
     * o maior plano real desta rede tem ~45k caracteres, então 100k dá folga de
     * mais de 2x. Cabe sem aperto — pior caso do prompt inteiro (DCRR 150k +
     * referências 60k + plano 100k) dá ~88 mil tokens contra 200 mil de contexto.
     * Um parecer errado por texto faltando custa muito mais que tokens.
     */
    private const MAX_PLAN_CHARS = 100000;

    /**
     * Teto da RESPOSTA. Em 4096 um parecer longo (polivalente, com correções
     * numeradas por dia e a mensagem ao professor) era cortado no meio da frase.
     */
    private const MAX_PARECER_TOKENS = 8192;

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
        // Teto generoso: um plano quinzenal polivalente (10 dias × 7 componentes)
        // passa fácil de 12k caracteres, que era o limite anterior. O que ficava
        // de fora era invisível para a IANNE, que então apontava como AUSENTE
        // uma parte que existia — o pior erro que este parecer pode cometer.
        $planCompleto = $document->content_text ?? '';
        $plan = mb_substr($planCompleto, 0, self::MAX_PLAN_CHARS);
        $planCortado = mb_strlen($planCompleto) > self::MAX_PLAN_CHARS;

        $avisoCorte = $planCortado
            ? "\n\n[ATENÇÃO: o texto acima é o INÍCIO do planejamento; ele foi cortado por limite de tamanho e há mais conteúdo além deste ponto. NÃO afirme que algo está ausente ou faltando com base no que não aparece aqui — se um item esperado não estiver no trecho, escreva \"não localizado no trecho analisado\" e recomende conferência manual do restante.]"
            : '';
        $professor = $document->user->name ?? 'não identificado';
        $turma = $document->schoolClass->name ?? 'não informada';
        $disciplina = $document->discipline ?? 'não informada';
        $vigencia = trim($context['vigencia'] ?? '') ?: ($document->reference_label ?? 'não informado');
        $aulas = trim($context['aulas'] ?? '') ?: 'não informado';
        $observacoes = trim($context['observacoes'] ?? '') ?: 'nenhuma';
        $dcrr = trim($context['dcrr'] ?? '');

        // Blocos editáveis pelo SuperAdmin (tela "Prompts da IANNE" do município).
        $b = PromptSettings::for($document->tenant_id);
        $extra = trim($b['parecer_extra']) !== '' ? "

INSTRUÇÕES ADICIONAIS DESTA REDE:
{$b['parecer_extra']}" : '';

        // Material enviado pela rede (SuperAdmin -> município -> Prompts da IANNE).
        $referencias = trim($context['referencias'] ?? '');
        $referenciasBloco = $referencias !== ''
            ? "MATERIAL DE REFERÊNCIA DESTA REDE (modelo de requisitos, portarias e rubricas enviados pela administração):\n{$referencias}\n\n"
                . "COMO USAR O MATERIAL ACIMA: as exigências dele são OBRIGATÓRIAS para esta rede e têm prioridade sobre orientações genéricas. Verifique o plano contra elas e, ao apontar uma falta, cite o documento de origem pelo título. Se o material for um recorte e algo não aparecer nele, NÃO afirme que a exigência não existe — diga que não foi localizada no material fornecido.\n\n"
            : '';

        $dcrrBloco = $dcrr !== ''
            ? "MATERIAL DE REFERÊNCIA DO DCRR (currículo de Roraima) para este componente/etapa:\n{$dcrr}\n\n"
                . "REGRA CRÍTICA SOBRE O MATERIAL ACIMA: ele é um RECORTE do DCRR. Se uma habilidade citada no plano não aparecer nele, NUNCA afirme que ela \"não existe no DCRR\" — escreva \"não localizada no trecho fornecido do DCRR; confirmar no documento completo\". Só aponte uma habilidade como inválida se o próprio CÓDIGO for incompatível com o ano/componente (padrão: EF + ano com 2 dígitos + sigla do componente + número; ex: EF05LP05 = 5º ano, Língua Portuguesa). Afirmar inexistência sem certeza induz o coordenador a erro — é a falha mais grave que este parecer pode cometer.\n"
            : "MATERIAL DO DCRR: NÃO fornecido. No item de alinhamento com o DCRR, escreva exatamente que não foi possível verificar por falta do documento de referência — NÃO invente habilidades, códigos ou alinhamentos do DCRR.\n";

        $prompt = <<<PROMPT
{$b['parecer_persona']}

REGRAS DE CALENDÁRIO (obrigatórias):
{$b['parecer_periodicidade']}
- Sábados e domingos NÃO são dias letivos. NUNCA aponte "falta de planejamento" para fim de semana.
- Ao verificar a cobertura da vigência, conte apenas os dias úteis (segunda a sexta) dentro do período.
- EXCEÇÕES: as "Observações do coordenador" têm PRIORIDADE sobre as regras acima. Se ele informar feriado, recesso ou dia sem aula, NÃO cobre planejamento para essa data; se informar sábado letivo ou reposição, esse dia PASSA a contar como letivo e deve ter planejamento. Ajuste a contagem de dias letivos conforme essas informações.

DADOS INFORMADOS PELO COORDENADOR:
- Professor(a): {$professor}
- Turma/ano: {$turma}
- Componente curricular: {$disciplina}
- Período de vigência informado: {$vigencia}
- Nº de aulas previstas: {$aulas}
- Observações do coordenador: {$observacoes}

{$dcrrBloco}
{$referenciasBloco}TEXTO DO PLANEJAMENTO:
{$plan}{$avisoCorte}

REGRA DE CORREÇÃO DE HABILIDADES: sempre que apontar erro de habilidade (código inexistente, de outro ano/componente, ou incompatível com o objeto de conhecimento/conteúdo trabalhado), INDIQUE a habilidade CORRETA para o professor substituir: localize no material do DCRR fornecido a habilidade adequada ao conteúdo e ao ano, e cite o código com um resumo curto da descrição (ex: "substituir por EF05MA08 — resolver problemas de multiplicação e divisão..."). Se a habilidade correta não estiver no material fornecido, oriente o professor a consultar a seção do DCRR daquele ano/componente — NUNCA invente um código.

CRITÉRIOS A VERIFICAR (checklist interno — não os copie como seções do parecer):
{$b['parecer_criterios']}

FORMATO DO PARECER (markdown, em português — seja direto, sem elogios no meio da análise):

1. "## ⚠️ Erros graves" — inclua esta seção SOMENTE se houver ao menos um destes problemas (caso contrário, omita a seção por completo):
{$b['parecer_erros_graves']}

2. "## Pontos a melhorar" — liste APENAS o que precisa ser corrigido ou está faltando, em itens objetivos, citando trechos do plano quando útil. NÃO descreva o que está adequado. Critério sem ressalvas não deve ser mencionado.

3. "## Pontos positivos" — curto e objetivo, ao final.

4. "**Situação geral:** " + uma única frase (ex: apto com pequenos ajustes / precisa de correções antes de aprovar / adequado).

5. "## Mensagem ao professor" — um resumo pronto para envio, que o coordenador poderá encaminhar (ou não, a critério dele) ao professor. Regras desta seção:
   - Escreva dirigindo-se diretamente ao professor, em tom respeitoso e objetivo (ex: "Professor(a), segue o retorno do seu planejamento...").
   - Liste as correções a fazer NUMERADAS (1., 2., 3...), uma por linha.
   - Em cada item, rastreie a correção pela DATA/dia da aula e pela DISCIPLINA sempre que o plano permitir (ex: "1. Aula de Matemática de 03/06: incluir o instrumento de avaliação da atividade de frações").
   - Quando a correção for de habilidade, inclua no item o código errado e o código correto sugerido (conforme a regra de correção de habilidades acima), para o professor só substituir.
   - A mensagem deve ser autossuficiente: o professor precisa entender o que corrigir sem ler o restante do parecer.
   - Se não houver nada a corrigir, escreva uma mensagem curta parabenizando e confirmando que o plano foi aprovado sem ressalvas.{$extra}
PROMPT;

        // maxTokens alto (o parecer é longo) e timeout generoso.
        $parecer = $this->ai->query($prompt, false, self::MAX_PARECER_TOKENS, 180);

        // Parecer cortado no limite de saída para de vez de sumir em silêncio:
        // o coordenador precisa saber que o texto acabou por limite, e não
        // porque a IANNE não tinha mais nada a apontar.
        if ($this->ai->lastStopReason() === 'max_tokens') {
            $parecer .= "

---

> **Este parecer foi interrompido por limite de tamanho e está incompleto.** "
                . "O que aparece acima é válido, mas pode haver mais itens não listados. "
                . "Clique em \"Refazer análise\" ou avalie o restante manualmente.";
        }

        return $parecer;
    }
}
