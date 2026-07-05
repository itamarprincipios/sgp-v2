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
            ? "MATERIAL DE REFERÊNCIA DO DCRR (currículo de Roraima) para este componente/etapa:\n{$dcrr}\n\n"
                . "REGRA CRÍTICA SOBRE O MATERIAL ACIMA: ele é um RECORTE do DCRR. Se uma habilidade citada no plano não aparecer nele, NUNCA afirme que ela \"não existe no DCRR\" — escreva \"não localizada no trecho fornecido do DCRR; confirmar no documento completo\". Só aponte uma habilidade como inválida se o próprio CÓDIGO for incompatível com o ano/componente (padrão: EF + ano com 2 dígitos + sigla do componente + número; ex: EF05LP05 = 5º ano, Língua Portuguesa). Afirmar inexistência sem certeza induz o coordenador a erro — é a falha mais grave que este parecer pode cometer.\n"
            : "MATERIAL DO DCRR: NÃO fornecido. No item de alinhamento com o DCRR, escreva exatamente que não foi possível verificar por falta do documento de referência — NÃO invente habilidades, códigos ou alinhamentos do DCRR.\n";

        $prompt = <<<PROMPT
Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima avaliando um PLANEJAMENTO QUINZENAL. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do plano e no material de referência fornecido. NÃO invente habilidades, códigos da BNCC nem alinhamentos que não estejam comprovados; quando algo não constar no plano, escreva "não consta".

REGRAS DE CALENDÁRIO (obrigatórias):
- O planejamento quinzenal cobre 10 DIAS LETIVOS, de segunda a sexta-feira, ao longo de duas semanas.
- Sábados e domingos NÃO são dias letivos. NUNCA aponte "falta de planejamento" para fim de semana.
- Ao verificar a cobertura da vigência, conte apenas os dias úteis (segunda a sexta) dentro do período.

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

CRITÉRIOS A VERIFICAR (checklist interno — não os copie como seções do parecer):
identificação completa do cabeçalho; vigência × dias letivos × nº de aulas; habilidades BNCC (códigos existem e pertencem ao ano/componente); CRUZAMENTO objetos de conhecimento × habilidades (cada habilidade citada deve ter objeto de conhecimento correspondente e vice-versa — aponte pares incompatíveis usando o material do DCRR); alinhamento ao DCRR; objetivos claros e mensuráveis; metodologia variada, adequada à faixa etária e DIVIDIDA EM MOMENTOS (acolhida/introdução, desenvolvimento, fechamento); avaliação com instrumentos e critérios definidos (incluindo formativa); inclusão e adaptações; coerência geral entre as partes.

FORMATO DO PARECER (markdown, em português — seja direto, sem elogios no meio da análise):

1. "## ⚠️ Erros graves" — inclua esta seção SOMENTE se houver ao menos um destes problemas (caso contrário, omita a seção por completo):
   - erro grave de metodologia (atividade incompatível com o ano/faixa etária ou erro conceitual);
   - AUSÊNCIA de forma/instrumento de avaliação;
   - metodologia NÃO dividida em momentos;
   - habilidade BNCC inexistente ou de outro ano/componente.

2. "## Pontos a melhorar" — liste APENAS o que precisa ser corrigido ou está faltando, em itens objetivos, citando trechos do plano quando útil. NÃO descreva o que está adequado. Critério sem ressalvas não deve ser mencionado.

3. "## Pontos positivos" — curto e objetivo, ao final.

4. "**Situação geral:** " + uma única frase (ex: apto com pequenos ajustes / precisa de correções antes de aprovar / adequado).

5. "## Mensagem ao professor" — um resumo pronto para envio, que o coordenador poderá encaminhar (ou não, a critério dele) ao professor. Regras desta seção:
   - Escreva dirigindo-se diretamente ao professor, em tom respeitoso e objetivo (ex: "Professor(a), segue o retorno do seu planejamento...").
   - Liste as correções a fazer NUMERADAS (1., 2., 3...), uma por linha.
   - Em cada item, rastreie a correção pela DATA/dia da aula e pela DISCIPLINA sempre que o plano permitir (ex: "1. Aula de Matemática de 03/06: incluir o instrumento de avaliação da atividade de frações").
   - A mensagem deve ser autossuficiente: o professor precisa entender o que corrigir sem ler o restante do parecer.
   - Se não houver nada a corrigir, escreva uma mensagem curta parabenizando e confirmando que o plano foi aprovado sem ressalvas.
PROMPT;

        // maxTokens alto (o parecer é longo) e timeout generoso.
        return $this->ai->query($prompt, false, 4096, 120);
    }
}
