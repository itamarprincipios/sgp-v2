<?php

namespace App\Services;

use App\Models\Document;

/**
 * Fase 2 do pivô — correção assistida. Monta a rubrica de avaliação e pede à
 * IANNE um parecer honesto. Não inventa DCRR/BNCC: quando falta material de
 * referência, instrui o modelo a declarar "não verificado".
 *
 * A rubrica é escolhida pelo TIPO do documento (documents.type, confirmado pelo
 * coordenador no upload). Antes, tudo era corrigido como planejamento — um
 * relatório voltava "todo errado" porque era cobrado por dias letivos e
 * metodologia em momentos, que ele nunca teve.
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

        // Regra de habilidades é comum: relatório de AEE também cita código BNCC.
        $regraHabilidades = 'REGRA DE CORREÇÃO DE HABILIDADES: sempre que apontar erro de habilidade (código inexistente, de outro ano/componente, ou incompatível com o objeto de conhecimento/conteúdo trabalhado), INDIQUE a habilidade CORRETA para substituir: localize no material do DCRR fornecido a habilidade adequada ao conteúdo e ao ano, e cite o código com um resumo curto da descrição (ex: "substituir por EF05MA08 — resolver problemas de multiplicação e divisão..."). Se a habilidade correta não estiver no material fornecido, oriente a consultar a seção do DCRR daquele ano/componente — NUNCA invente um código.';

        $dados = <<<BLOCO_DADOS
DADOS INFORMADOS PELO COORDENADOR:
- Professor(a): {$professor}
- Turma/ano: {$turma}
- Componente curricular: {$disciplina}
- Período de referência informado: {$vigencia}
- Nº de aulas previstas: {$aulas}
- Observações do coordenador: {$observacoes}
BLOCO_DADOS;

        $comum = [
            'dados' => $dados,
            'dcrrBloco' => $dcrrBloco,
            'referenciasBloco' => $referenciasBloco,
            'texto' => $plan . $avisoCorte,
            'regraHabilidades' => $regraHabilidades,
        ];

        $prompt = match ($document->type) {
            'relatorio' => $this->promptRelatorio($b, $comum),
            'pei' => $this->promptPei($b, $comum),
            'outro' => $this->promptOutro($b, $comum),
            default => $this->promptPlanejamento($b, $comum),
        };

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

    /**
     * Rubrica de PLANEJAMENTO — a original. Cobra dias letivos, momentos da
     * metodologia e instrumentos de avaliação.
     */
    private function promptPlanejamento(array $b, array $c): string
    {
        $extra = $this->blocoExtra($b['parecer_extra'] ?? '');

        return <<<PROMPT
{$b['parecer_persona']}

REGRAS DE CALENDÁRIO (obrigatórias):
{$b['parecer_periodicidade']}
- Sábados e domingos NÃO são dias letivos. NUNCA aponte "falta de planejamento" para fim de semana.
- Ao verificar a cobertura da vigência, conte apenas os dias úteis (segunda a sexta) dentro do período.
- EXCEÇÕES: as "Observações do coordenador" têm PRIORIDADE sobre as regras acima. Se ele informar feriado, recesso ou dia sem aula, NÃO cobre planejamento para essa data; se informar sábado letivo ou reposição, esse dia PASSA a contar como letivo e deve ter planejamento. Ajuste a contagem de dias letivos conforme essas informações.

{$c['dados']}

{$c['dcrrBloco']}
{$c['referenciasBloco']}TEXTO DO PLANEJAMENTO:
{$c['texto']}

{$c['regraHabilidades']}

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
    }

    /**
     * Rubrica de RELATÓRIO de acompanhamento de aluno (AEE, educação infantil).
     *
     * Três coisas que o parecer de planejamento não tem, e que definem este aqui:
     * o alerta de saúde vem ANTES de tudo; todo termo capacitista encontrado vira
     * reescrita proposta, não só apontamento; e é proibido cobrar estrutura de
     * planejamento de um documento que nunca se propôs a ter uma.
     */
    private function promptRelatorio(array $b, array $c): string
    {
        $extra = $this->blocoExtra($b['relatorio_extra'] ?? '');

        $termos = trim($b['relatorio_termos_vetados'] ?? '');
        $listaTermos = $termos !== ''
            ? $termos
            : '(nenhum termo cadastrado — use seu próprio julgamento sobre linguagem capacitista)';

        return <<<PROMPT
{$b['relatorio_persona']}

REGRA DE ESCOPO (obrigatória): este documento é um RELATÓRIO, não um planejamento. NUNCA cobre dele: cobertura de dias letivos, divisão da metodologia em momentos, quantidade de aulas, ou qualquer elemento próprio de plano de aula. Cobrar isso de um relatório invalida o parecer inteiro.

{$c['dados']}

{$c['dcrrBloco']}
{$c['referenciasBloco']}TEXTO DO RELATÓRIO:
{$c['texto']}

{$c['regraHabilidades']}

TERMOS CAPACITISTAS OU DEFICITÁRIOS A SINALIZAR (procure estes e QUALQUER equivalente de mesmo sentido):
{$listaTermos}

CRITÉRIOS A VERIFICAR (checklist interno — não os copie como seções do parecer):
{$b['relatorio_criterios']}

FORMATO DO PARECER (markdown, em português — seja direto, sem elogios no meio da análise):

1. "## 🚨 Atenção: sinal de saúde" — inclua SOMENTE se o relatório mencionar dor, desconforto, perda ou falta de mobilidade, crise, regressão física ou outro sinal clínico. Cite o trecho exato e recomende o diálogo com a família e o encaminhamento à área da saúde. Se não houver nada disso, OMITA a seção por completo. Quando houver, esta seção vem ANTES de todas as outras — é a informação mais urgente do documento.

2. "## ⚠️ Erros graves" — inclua SOMENTE se houver ao menos um destes problemas (caso contrário, omita por completo):
{$b['relatorio_erros_graves']}

3. "## Pontos a melhorar" — liste APENAS o que precisa ser corrigido ou está faltando, em itens objetivos, citando trechos do relatório. NÃO descreva o que está adequado.

4. "## Reescrita sugerida" — inclua SOMENTE se houver termo capacitista/deficitário ou trecho de redação problemática. Para cada ocorrência, uma linha no formato:
   - *Onde está:* "trecho original" → *Trocar por:* "reescrita proposta"
   A reescrita deve focar na NECESSIDADE DE SUPORTE e na MEDIAÇÃO COMUNICATIVA, nunca no déficit do aluno. Ex: "não consegue ler sozinho" → "realiza a leitura com apoio de mediação e material adaptado".

5. "## Correções de escrita" — erros de ortografia e sintaxe, campos vazios ou inaplicáveis. Lista curta. Omita se não houver.

6. "## Pontos positivos" — curto e objetivo.

7. "**Situação geral:** " + uma única frase (ex: apto com pequenos ajustes / precisa de correções antes de aprovar / adequado).

8. "## Mensagem ao professor" — resumo pronto para envio, que o coordenador poderá encaminhar ao professor. Regras desta seção:
   - Dirija-se diretamente ao professor, em tom respeitoso e objetivo.
   - Liste as correções NUMERADAS (1., 2., 3...), uma por linha.
   - Em cada item, localize o ponto pelo campo ou seção do relatório (ex: "2. No campo 'Parecer conclusivo': a conclusão diz que o aluno não participou, mas o campo de frequência registra 92% — ajustar uma das duas informações").
   - Quando o item for de linguagem, inclua o trecho original E a reescrita proposta, para o professor só substituir.
   - Se o relatório trouxer sinal de saúde, mencione o encaminhamento à família logo no início da mensagem.
   - A mensagem deve ser autossuficiente.
   - Se não houver nada a corrigir, escreva uma mensagem curta confirmando que o relatório foi aprovado sem ressalvas.{$extra}
PROMPT;
    }

    /**
     * Rubrica de PEI — Plano Educacional Individualizado.
     *
     * Híbrido: planeja como um plano, mas é sobre uma pessoa como um relatório.
     * O que define este parecer: a caça a CONTRADIÇÕES entre o perfil e as metas
     * (habilidade dada como consolidada reaparecendo como necessidade), a
     * reescrita de toda meta não mensurável, e a mesma cautela do DCRR aplicada
     * ao CID — a IANNE não tem a tabela completa e não pode decretar inexistência.
     */
    private function promptPei(array $b, array $c): string
    {
        $extra = $this->blocoExtra($b['pei_extra'] ?? '');

        $termos = trim($b['relatorio_termos_vetados'] ?? '');
        $blocoTermos = $termos !== ''
            ? "TERMOS CAPACITISTAS OU DEFICITÁRIOS A SINALIZAR (procure estes e QUALQUER equivalente de mesmo sentido — o PEI é validado pela família):\n{$termos}\n"
            : '';

        return <<<PROMPT
{$b['pei_persona']}

REGRA DE ESCOPO (obrigatória): este documento é um PEI. NUNCA cobre dele cobertura de dias letivos, divisão da metodologia em momentos ou número de aulas — isso é de plano de aula quinzenal e não se aplica. Cobre dele o que é próprio do PEI: metas individualizadas mensuráveis, estratégias, recursos, papéis e revisão.

REGRA CRÍTICA SOBRE CÓDIGOS CID: você NÃO dispõe da tabela CID-10/CID-11 completa. NUNCA afirme que um código CID "não existe" ou "está errado" por não reconhecê-lo. Você PODE apontar: código com formato claramente inválido; dois códigos que descrevem o mesmo quadro (duplicidade conceitual); incoerência entre o código informado e o quadro descrito no texto; ou ausência do código quando o documento tem campo para ele. Fora disso, escreva "não verificável — confirmar com o laudo". Decretar inexistência de um CID induz a escola a erro diante da família.

{$c['dados']}

{$c['dcrrBloco']}
{$c['referenciasBloco']}TEXTO DO PEI:
{$c['texto']}

{$c['regraHabilidades']}

{$blocoTermos}
CRITÉRIOS A VERIFICAR (checklist interno — não os copie como seções do parecer):
{$b['pei_criterios']}

FORMATO DO PARECER (markdown, em português — seja direto, sem elogios no meio da análise):

1. "## ⚠️ Erros graves" — inclua SOMENTE se houver ao menos um destes problemas (caso contrário, omita por completo):
{$b['pei_erros_graves']}

2. "## Contradições encontradas" — inclua SOMENTE se houver. Para cada uma, cite OS DOIS trechos que se contradizem, no formato:
   - *Na seção X:* "trecho" — *mas na seção Y:* "trecho". *Conflito:* explicação em uma linha.
   Omita a seção se o documento for coerente.

3. "## Metas a reescrever" — inclua SOMENTE se houver meta sem critério observável. Para cada uma, uma linha no formato:
   - *Como está:* "meta original" → *Reescrever como:* "meta com critério de tempo, frequência ou percentual"
   Ex: "desenvolver autonomia na alimentação" → "alimentar-se sozinho(a) com talher adaptado em 4 de 5 refeições na escola, até a revisão de junho".

4. "## Pontos a melhorar" — o que mais precisa de correção, em itens objetivos, citando trechos. Inclua aqui: componentes curriculares ausentes, hiperfoco não aproveitado didaticamente, recursos genéricos demais, papéis indefinidos, confusão entre atribuição pedagógica e terapia clínica.

5. "## Formalidade" — gramática, pontuação, campos de assinatura e validação da família. Lista curta. Omita se não houver.

6. "## Pontos positivos" — curto e objetivo.

7. "**Situação geral:** " + uma única frase (ex: apto com pequenos ajustes / precisa de correções antes de aprovar / adequado).

8. "## Mensagem ao professor" — resumo pronto para envio ao professor responsável pelo PEI. Regras desta seção:
   - Dirija-se diretamente ao professor, em tom respeitoso e objetivo.
   - Liste as correções NUMERADAS (1., 2., 3...), uma por linha.
   - Localize cada item pela seção ou campo do PEI.
   - Quando o item for meta não mensurável, traga a meta reescrita pronta para substituir.
   - Quando for contradição, cite os dois trechos para o professor decidir qual vale.
   - A mensagem deve ser autossuficiente.
   - Se não houver nada a corrigir, escreva uma mensagem curta confirmando que o PEI foi aprovado sem ressalvas.{$extra}
PROMPT;
    }

    /**
     * Documento que não é planejamento, relatório nem PEI. Deliberadamente
     * modesto: avalia o documento pelo que ele se propõe a ser, em vez de impor
     * uma régua que não serve.
     */
    private function promptOutro(array $b, array $c): string
    {
        return <<<PROMPT
Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima. O documento abaixo NÃO foi classificado como planejamento nem como relatório de aluno, então você ainda não sabe que régua aplicar.

{$b['outro_criterios']}

{$c['dados']}

{$c['dcrrBloco']}
{$c['referenciasBloco']}TEXTO DO DOCUMENTO:
{$c['texto']}

{$c['regraHabilidades']}

FORMATO DO PARECER (markdown, em português):

1. "## Tipo identificado" — uma frase dizendo que documento você entendeu que é.
2. "## Pontos a melhorar" — apenas o que precisa de correção, em itens objetivos.
3. "**Situação geral:** " + uma única frase.
4. "## Mensagem ao professor" — resumo pronto para envio, com as correções numeradas.

Ao final, acrescente em itálico: *"Este documento não se encaixa nas rubricas de planejamento nem de relatório. Se este tipo for frequente nesta rede, peça ao administrador do sistema para cadastrar uma rubrica específica."*
PROMPT;
    }

    private function blocoExtra(string $texto): string
    {
        $texto = trim($texto);

        return $texto !== '' ? "\n\nINSTRUÇÕES ADICIONAIS DESTA REDE:\n{$texto}" : '';
    }
}
