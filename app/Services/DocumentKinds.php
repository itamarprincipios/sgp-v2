<?php

namespace App\Services;

use App\Models\Document;

/**
 * Catálogo dos tipos de documento que a IANNE analisa.
 *
 * Até aqui a Central de Correção só sabia corrigir planejamento. O coordenador,
 * porém, recebe pelo mesmo WhatsApp o PEI, o relatório bimestral do professor,
 * o relatório do aluno da educação especial e o relatório descritivo da
 * educação infantil — e cada um se corrige por critérios diferentes. O campo de
 * envio continua UM só: quem identifica o tipo é a IANNE, no momento do upload
 * (MetadataInference), e o coordenador apenas confirma.
 *
 * Cada tipo declara aqui:
 *  - como é reconhecido no texto (vai no prompt da inferência);
 *  - que campos o card de confirmação precisa pedir (aluno, turma, disciplina);
 *  - se o parecer usa DCRR e as regras de calendário (só planejamento);
 *  - a persona e as regras fixas do parecer daquele tipo;
 *  - quais blocos do PromptSettings trazem critérios/erros graves (editáveis
 *    pelo SuperAdmin por município).
 *
 * `type` é o balde antigo da coluna `documents.type` (enum planejamento/
 * relatorio/outro), mantido para não quebrar relatórios e telas legadas. O tipo
 * fino fica em `documents.doc_kind`.
 */
class DocumentKinds
{
    public const DEFAULT = 'planejamento';

    public const KINDS = [
        'planejamento' => [
            'label' => 'Planejamento de aula',
            'badge' => 'Planejamento',
            'type' => 'planejamento',
            'pede_aluno' => false,
            'pede_turma' => true,
            'pede_disciplina' => true,
            'usa_dcrr' => true,
            'usa_calendario' => true,
            'pede_aulas' => true,
            'identificacao' => 'Plano de aula de uma turma: traz datas/dias de aula, objetivos, conteúdos ou objetos de conhecimento, habilidades (BNCC/DCRR), metodologia e avaliação. Fala do que SERÁ feito (futuro), não do que já aconteceu.',
            'persona' => null, // usa o bloco editável parecer_persona
            'regras' => '',
            'blocos' => ['criterios' => 'parecer_criterios', 'erros' => 'parecer_erros_graves'],
        ],

        'pei' => [
            'label' => 'PEI — Plano Educacional Individualizado',
            'badge' => 'PEI',
            'type' => 'planejamento',
            'pede_aluno' => true,
            'pede_turma' => true,
            'pede_disciplina' => false,
            'usa_dcrr' => true,
            'usa_calendario' => false,
            'pede_aulas' => false,
            'identificacao' => 'Plano individual de UM aluno público da educação especial: cita "PEI", "Plano Educacional Individualizado", "AEE", adaptações curriculares, metas individuais, laudo/diagnóstico. Planeja o atendimento de um aluno nomeado, não as aulas da turma.',
            'persona' => 'Você é um(a) coordenador(a) pedagógico(a) com experiência em educação especial inclusiva, da rede municipal de Roraima, avaliando um PEI (Plano Educacional Individualizado). Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do documento e no material de referência fornecido; quando algo não constar, escreva "não consta".',
            'regras' => "REGRAS FIXAS DESTE TIPO DE DOCUMENTO:\n"
                . "- O PEI é um plano DE UM ALUNO: metas que serviriam para qualquer criança são o principal defeito a apontar.\n"
                . "- NUNCA exija laudo médico como condição do atendimento: matrícula, AEE e adaptações não dependem de laudo (LBI/Política Nacional de Educação Especial). A falta de laudo não é erro do professor.\n"
                . "- Diagnóstico é da área da saúde. Se o documento trouxer o professor afirmando diagnóstico, aponte como erro.\n"
                . "- O PEI flexibiliza/adapta o currículo da turma; NUNCA sugira currículo paralelo nem rebaixar o aluno a atividades de outra etapa sem justificativa pedagógica.\n"
                . '- Use linguagem pessoa-primeiro ("aluno com deficiência", "aluno público da educação especial"), nunca rótulos.',
            'blocos' => ['criterios' => 'doc_pei_criterios', 'erros' => 'doc_pei_erros'],
        ],

        'relatorio_professor' => [
            'label' => 'Relatório do professor (turma/período)',
            'badge' => 'Relatório do professor',
            'type' => 'relatorio',
            'pede_aluno' => false,
            'pede_turma' => true,
            'pede_disciplina' => true,
            'usa_dcrr' => false,
            'usa_calendario' => false,
            'pede_aulas' => false,
            'identificacao' => 'Relatório descritivo que o professor escreve sobre a TURMA ao fim de um período (bimestre/semestre): conteúdos trabalhados, desenvolvimento e dificuldades da turma, encaminhamentos. Fala do que JÁ aconteceu (passado) e não é sobre um único aluno.',
            'persona' => 'Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima avaliando o RELATÓRIO DE PERÍODO que um professor escreveu sobre a sua turma. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do documento e no material de referência fornecido; quando algo não constar, escreva "não consta".',
            'regras' => "REGRAS FIXAS DESTE TIPO DE DOCUMENTO:\n"
                . "- O relatório vale pelo que permite DECIDIR: dificuldade relatada sem proposta de intervenção é a falha mais comum e precisa ser apontada.\n"
                . "- Cobre descrição com evidência (dados, exemplos, episódios), não adjetivos sobre os alunos.\n"
                . "- Aponte como erro qualquer trecho que culpabilize aluno ou família sem descrever o fato observado.\n"
                . '- Diagnóstico de deficiência ou transtorno não é atribuição do professor: encaminhamento sim, diagnóstico não.',
            'blocos' => ['criterios' => 'doc_rel_professor_criterios', 'erros' => 'doc_rel_professor_erros'],
        ],

        'relatorio_aluno_especial' => [
            'label' => 'Relatório de aluno da educação especial',
            'badge' => 'Relatório do aluno (ed. especial)',
            'type' => 'relatorio',
            'pede_aluno' => true,
            'pede_turma' => true,
            'pede_disciplina' => false,
            'usa_dcrr' => false,
            'usa_calendario' => false,
            'pede_aulas' => false,
            'identificacao' => 'Relatório descritivo de UM aluno público da educação especial: evolução no período, barreiras, recursos e adaptações usadas, relação com o PEI, AEE. É retrospectivo (o que já aconteceu) e individual — se for plano de metas futuras, é PEI.',
            'persona' => 'Você é um(a) coordenador(a) pedagógico(a) com experiência em educação especial inclusiva, da rede municipal de Roraima, avaliando o RELATÓRIO INDIVIDUAL de um aluno público da educação especial. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do documento e no material de referência fornecido; quando algo não constar, escreva "não consta".',
            'regras' => "REGRAS FIXAS DESTE TIPO DE DOCUMENTO:\n"
                . "- Evolução se descreve por EVIDÊNCIA (o que o aluno fez, em que situação, com qual apoio), não por adjetivo (\"evoluiu bem\", \"é carinhoso\").\n"
                . "- Diagnóstico é da área da saúde: o professor relata o que observa, não atribui nem altera diagnóstico/CID.\n"
                . "- Aponte como erro linguagem estigmatizante, comparação com os demais alunos e qualquer trecho que reduza o aluno ao diagnóstico.\n"
                . "- Presença não é aprendizagem: relatório que só registra que o aluno frequentou não cumpre a função.\n"
                . '- Use linguagem pessoa-primeiro ("aluno com deficiência"), nunca rótulos.',
            'blocos' => ['criterios' => 'doc_rel_aluno_criterios', 'erros' => 'doc_rel_aluno_erros'],
        ],

        'relatorio_educacao_infantil' => [
            'label' => 'Relatório de educação infantil (criança)',
            'badge' => 'Relatório da ed. infantil',
            'type' => 'relatorio',
            'pede_aluno' => true,
            'pede_turma' => true,
            'pede_disciplina' => false,
            'usa_dcrr' => true,
            'usa_calendario' => false,
            'pede_aulas' => false,
            'identificacao' => 'Relatório descritivo de criança da educação infantil (berçário, maternal, creche, pré-escola): brincadeiras, interações, campos de experiência da BNCC, rotina. Sem notas nem conceitos; a turma é um agrupamento por idade, não "1º ao 5º ano".',
            'persona' => 'Você é um(a) coordenador(a) pedagógico(a) de educação infantil da rede municipal de Roraima avaliando o RELATÓRIO DESCRITIVO de uma criança. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do documento e no material de referência fornecido; quando algo não constar, escreva "não consta".',
            'regras' => "REGRAS FIXAS DESTE TIPO DE DOCUMENTO:\n"
                . "- Na educação infantil NÃO há nota, conceito, aprovação nem retenção (DCNEI/BNCC): o registro é descritivo e processual. NUNCA sugira classificar a criança.\n"
                . "- A criança é comparada consigo mesma ao longo do tempo, jamais com as outras crianças ou com um padrão de turma.\n"
                . "- O que sustenta o relatório são EPISÓDIOS observados do cotidiano (falas, brincadeiras, interações), não adjetivos.\n"
                . "- Os cinco campos de experiência da BNCC são: \"O eu, o outro e o nós\"; \"Corpo, gestos e movimentos\"; \"Traços, sons, cores e formas\"; \"Escuta, fala, pensamento e imaginação\"; \"Espaços, tempos, quantidades, relações e transformações\". Aponte quais ficaram sem registro.\n"
                . '- O relatório é lido pela família: cobre linguagem acessível, respeitosa e sem jargão.',
            'blocos' => ['criterios' => 'doc_rel_infantil_criterios', 'erros' => 'doc_rel_infantil_erros'],
        ],

        'outro' => [
            'label' => 'Outro documento pedagógico',
            'badge' => 'Outro documento',
            'type' => 'outro',
            'pede_aluno' => false,
            'pede_turma' => true,
            'pede_disciplina' => true,
            'usa_dcrr' => false,
            'usa_calendario' => false,
            'pede_aulas' => false,
            'identificacao' => 'Qualquer documento pedagógico que não se encaixe nos anteriores (ata, projeto, plano de ação, ofício). Use este tipo apenas quando nenhum outro servir.',
            'persona' => 'Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima analisando um documento pedagógico enviado por um professor. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do documento e no material de referência fornecido; quando algo não constar, escreva "não consta".',
            'regras' => "REGRAS FIXAS DESTE TIPO DE DOCUMENTO:\n"
                . "- Comece o parecer dizendo, em uma frase, que documento você identificou — o coordenador pode ter escolhido o tipo errado na confirmação.\n"
                . '- Não cobre itens de planejamento, PEI ou relatório que este documento não se propõe a ter.',
            'blocos' => ['criterios' => null, 'erros' => null],
        ],
    ];

    /**
     * Critérios/erros graves usados quando o tipo não tem blocos editáveis
     * (caso de "outro"): rubrica mínima para o parecer não sair vazio.
     */
    private const RUBRICA_GENERICA = [
        'criterios' => 'identificação do documento (autor, turma/aluno, período, finalidade); objetivo explícito do documento; completude em relação ao que ele se propõe; coerência interna entre as partes; clareza e objetividade da escrita; presença do que a rede exige no material de referência, quando houver.',
        'erros' => "- documento sem identificação de autor, turma/aluno ou período;\n- documento que não deixa claro o próprio objetivo;\n- contradição interna entre as partes.",
    ];

    /**
     * Chave do tipo de um documento: `doc_kind` quando preenchido, senão
     * deduzida da coluna antiga `type` (documentos anteriores a esta mudança).
     */
    public static function keyFor(Document $document): string
    {
        return self::resolve($document->doc_kind, $document->type);
    }

    /**
     * @param string|null $kind valor de documents.doc_kind
     * @param string|null $type valor de documents.type (balde antigo)
     */
    public static function resolve(?string $kind, ?string $type = null): string
    {
        if ($kind !== null && isset(self::KINDS[$kind])) {
            return $kind;
        }

        return match ($type) {
            'relatorio' => 'relatorio_professor',
            'outro' => 'outro',
            default => self::DEFAULT,
        };
    }

    public static function exists(?string $kind): bool
    {
        return $kind !== null && isset(self::KINDS[$kind]);
    }

    /**
     * @return array definição do tipo (nunca null — cai no padrão)
     */
    public static function get(?string $kind): array
    {
        return self::KINDS[self::resolve($kind)];
    }

    public static function label(?string $kind): string
    {
        return self::get($kind)['label'];
    }

    public static function badge(?string $kind): string
    {
        return self::get($kind)['badge'];
    }

    /**
     * Balde da coluna antiga `documents.type` correspondente ao tipo fino.
     */
    public static function legacyType(?string $kind): string
    {
        return self::get($kind)['type'];
    }

    /**
     * @return string[] chaves válidas, na ordem do catálogo
     */
    public static function keys(): array
    {
        return array_keys(self::KINDS);
    }

    /**
     * Lista "chave: rótulo — como reconhecer" para o prompt da inferência.
     */
    public static function promptList(): string
    {
        $linhas = [];
        foreach (self::KINDS as $chave => $def) {
            $linhas[] = "- \"{$chave}\" ({$def['label']}): {$def['identificacao']}";
        }

        return implode("\n", $linhas);
    }

    /**
     * Rubrica em vigor para um tipo num município: o que o SuperAdmin editou,
     * ou o padrão. Tipos sem blocos editáveis usam a rubrica genérica.
     *
     * @param array<string, string> $blocos saída de PromptSettings::for()
     * @return array{criterios: string, erros: string}
     */
    public static function rubrica(?string $kind, array $blocos): array
    {
        $def = self::get($kind);

        $criteriosKey = $def['blocos']['criterios'] ?? null;
        $errosKey = $def['blocos']['erros'] ?? null;

        $criterios = $criteriosKey ? trim((string) ($blocos[$criteriosKey] ?? '')) : '';
        $erros = $errosKey ? trim((string) ($blocos[$errosKey] ?? '')) : '';

        return [
            'criterios' => $criterios !== '' ? $criterios : self::RUBRICA_GENERICA['criterios'],
            'erros' => $erros !== '' ? $erros : self::RUBRICA_GENERICA['erros'],
        ];
    }

    /**
     * Metadados que a tela de correções precisa para montar os campos do card
     * e do diálogo de análise de cada tipo.
     *
     * @return array<string, array>
     */
    public static function forFrontend(): array
    {
        $saida = [];
        foreach (self::KINDS as $chave => $def) {
            $saida[$chave] = [
                'label' => $def['label'],
                'badge' => $def['badge'],
                'pede_aluno' => $def['pede_aluno'],
                'pede_disciplina' => $def['pede_disciplina'],
                'pede_aulas' => $def['pede_aulas'],
                'usa_calendario' => $def['usa_calendario'],
            ];
        }

        return $saida;
    }
}
