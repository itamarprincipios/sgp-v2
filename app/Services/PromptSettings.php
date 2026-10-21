<?php

namespace App\Services;

use App\Models\TenantAiPrompt;

/**
 * Blocos editáveis dos prompts da IANNE, por município (tenant).
 *
 * O esqueleto dos prompts continua no código — regras anti-alucinação do DCRR,
 * formato do parecer, contrato JSON da inferência. Aqui ficam só os pedaços que
 * mudam de rede para rede (tom, critérios, periodicidade, regra de disciplina),
 * para o SuperAdmin afinar sem precisar de deploy.
 *
 * Bloco vazio ou não salvo = usa o padrão. Nunca devolve string vazia, senão o
 * prompt final sairia com um buraco no meio.
 */
class PromptSettings
{
    /**
     * Chave => [rótulo, explicação para quem edita, valor padrão].
     * A ordem aqui é a ordem em que a tela exibe os campos.
     */
    public const BLOCKS = [
        // ---------- Parecer do planejamento (PlanAnalyzer) ----------
        'parecer_persona' => [
            'grupo' => 'parecer',
            'label' => 'Quem a IANNE é e como escreve',
            'ajuda' => 'Abre o prompt. Define o papel e o tom do parecer.',
            'default' => 'Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima avaliando um PLANEJAMENTO QUINZENAL. Seja honesto(a), específico(a) e construtivo(a). Baseie-se SOMENTE no texto do plano e no material de referência fornecido. NÃO invente habilidades, códigos da BNCC nem alinhamentos que não estejam comprovados; quando algo não constar no plano, escreva "não consta".',
        ],
        'parecer_periodicidade' => [
            'grupo' => 'parecer',
            'label' => 'Periodicidade do planejamento',
            'ajuda' => 'Quantos dias o plano cobre. Mude aqui se a rede trabalhar com plano semanal, mensal ou bimestral em vez de quinzenal.',
            'default' => '- O planejamento quinzenal cobre 10 DIAS LETIVOS, de segunda a sexta-feira, ao longo de duas semanas.',
        ],
        'parecer_criterios' => [
            'grupo' => 'parecer',
            'label' => 'Critérios a verificar',
            'ajuda' => 'A rubrica. É o checklist interno que a IANNE percorre — ele não vira seção do parecer. Separe os critérios por ponto e vírgula.',
            'default' => 'identificação completa do cabeçalho; vigência × dias letivos × nº de aulas; habilidades BNCC (códigos existem e pertencem ao ano/componente); CRUZAMENTO objetos de conhecimento × habilidades (cada habilidade citada deve ter objeto de conhecimento correspondente e vice-versa — aponte pares incompatíveis usando o material do DCRR); alinhamento ao DCRR; objetivos claros e mensuráveis; metodologia variada, adequada à faixa etária e DIVIDIDA EM MOMENTOS (acolhida/introdução, desenvolvimento, fechamento); avaliação com instrumentos e critérios definidos (incluindo formativa); inclusão e adaptações; coerência geral entre as partes.',
        ],
        'parecer_erros_graves' => [
            'grupo' => 'parecer',
            'label' => 'O que conta como erro grave',
            'ajuda' => 'A seção "⚠️ Erros graves" só aparece no parecer se um destes for encontrado. Um item por linha, começando com hífen.',
            'default' => "- erro grave de metodologia (atividade incompatível com o ano/faixa etária ou erro conceitual);\n- AUSÊNCIA de forma/instrumento de avaliação;\n- metodologia NÃO dividida em momentos;\n- habilidade BNCC inexistente ou de outro ano/componente.",
        ],
        'parecer_extra' => [
            'grupo' => 'parecer',
            'label' => 'Instruções adicionais (opcional)',
            'ajuda' => 'Texto livre acrescentado ao final do prompt. Use para exigências próprias da rede. Deixe vazio se não precisar.',
            'default' => '',
        ],

        // ---------- Parecer de RELATÓRIO (PlanAnalyzer, quando type = relatorio) ----------
        'relatorio_persona' => [
            'grupo' => 'relatorio',
            'label' => 'Quem a IANNE é e como escreve',
            'ajuda' => 'Abre o prompt do relatório. Relatório é texto sobre uma pessoa, não uma estrutura de dias — o tom é diferente do parecer de planejamento.',
            'default' => 'Você é um(a) coordenador(a) pedagógico(a) experiente da rede municipal de Roraima avaliando um RELATÓRIO PEDAGÓGICO de acompanhamento de aluno (frequentemente de Atendimento Educacional Especializado). Este documento NÃO é um planejamento: não cobre dias letivos nem sequência de aulas, e NUNCA deve ser cobrado como se fosse. Avalie o que o texto diz sobre o aluno. Seja honesto(a), específico(a) e construtivo(a), lembrando que este documento pode ser lido pela família e integrar o histórico escolar do estudante.',
        ],
        'relatorio_criterios' => [
            'grupo' => 'relatorio',
            'label' => 'Critérios a verificar',
            'ajuda' => 'A rubrica do relatório. Checklist interno — não vira seção do parecer. Um critério por linha.',
            'default' => "1. CONSISTÊNCIA CURRICULAR: os códigos BNCC/DCRR citados correspondem ao conteúdo efetivamente ministrado? Todas as habilidades pertencem ao ano/etapa do aluno, sem inserção indevida de ciclos superiores? Há códigos repetidos a eliminar?\n"
                . "2. FOCO NA APRENDIZAGEM INDIVIDUAL: o texto descreve mais o que a TURMA fez ou a RESPOSTA DO ALUNO? Aponte quando o relatório for predominantemente sobre a turma. Especifica o recurso de apoio, a tecnologia assistiva ou a mediação do monitor empregada?\n"
                . "3. LINGUAGEM E POSTURA PEDAGÓGICA: há termos capacitistas ou deficitários (ver lista abaixo)? Para cada ocorrência, proponha reescrita com foco na necessidade de suporte e na mediação comunicativa.\n"
                . "4. COERÊNCIA TEXTUAL E LÓGICA: o parecer conclusivo contradiz dados específicos de frequência ou de participação informados nos componentes? Aponte a contradição citando os dois trechos.\n"
                . "5. ASPECTOS FÍSICOS E ENCAMINHAMENTOS: há menção a dor, desconforto, falta de mobilidade ou outro sinal clínico? Se houver, cobre explicitamente o diálogo com a família e com a área da saúde.\n"
                . "6. PADRONIZAÇÃO FORMAL E GRAMÁTICA: há campos vazios, campos inaplicáveis preenchidos ou deixados em branco sem justificativa? Há erros de ortografia e sintaxe? Liste-os.\n"
                . "7. QUALIDADE DO PLANO DE INTERVENÇÃO: as sugestões finais para o próximo ciclo são acionáveis e adaptadas ao perfil do estudante (TEA, DI ou outro)? Sugestão genérica não serve — aponte.",
        ],
        'relatorio_termos_vetados' => [
            'grupo' => 'relatorio',
            'label' => 'Termos capacitistas a sinalizar',
            'ajuda' => 'A IANNE procura estes termos E equivalentes de mesmo sentido. Um por linha. Toda ocorrência vira reescrita sugerida.',
            'default' => "não se entende nada\nnão tem autonomia\nentediado\nnão consegue\nnão acompanha\nincapaz\nnão rende\nnão participa de nada\nnão evolui",
        ],
        'relatorio_erros_graves' => [
            'grupo' => 'relatorio',
            'label' => 'O que conta como erro grave',
            'ajuda' => 'A seção "⚠️ Erros graves" só aparece se um destes for encontrado. Um item por linha, começando com hífen.',
            'default' => "- habilidade BNCC/DCRR inexistente, de outro ano/etapa, ou incompatível com o conteúdo descrito;\n"
                . "- relatório que descreve a turma e não o aluno, sem registro da resposta individual dele;\n"
                . "- parecer conclusivo que CONTRADIZ os dados de frequência ou participação informados no próprio documento;\n"
                . "- termo capacitista ou deficitário em documento que vai ao histórico do aluno e pode ser lido pela família;\n"
                . "- ausência completa de sugestões de intervenção para o próximo ciclo.",
        ],
        'relatorio_extra' => [
            'grupo' => 'relatorio',
            'label' => 'Instruções adicionais (opcional)',
            'ajuda' => 'Texto livre acrescentado ao final do prompt do relatório. Deixe vazio se não precisar.',
            'default' => '',
        ],

        // ---------- Parecer de documento NÃO classificado (type = outro) ----------
        'outro_criterios' => [
            'grupo' => 'outro',
            'label' => 'Critérios para documento de tipo desconhecido',
            'ajuda' => 'Usado quando o documento não é planejamento nem relatório. Deliberadamente genérico: melhor um parecer modesto que uma régua errada.',
            'default' => "Avalie o documento PELO QUE ELE SE PROPÕE A SER, deduzindo isso do próprio texto — não o cobre como planejamento nem como relatório.\n"
                . "- Diga, na primeira linha, que tipo de documento você identificou.\n"
                . "- Verifique: completude (campos ou seções iniciados e não preenchidos), coerência interna, clareza, ortografia e sintaxe.\n"
                . "- Se houver códigos BNCC/DCRR, confira se existem e pertencem ao ano/componente citado.\n"
                . "- NÃO cobre elementos que o documento não se propõe a ter.",
        ],

        // ---------- IANNE Assistente (chat da escola) ----------
        'assistente_persona' => [
            'grupo' => 'assistente',
            'label' => 'Quem a IANNE é no chat',
            'ajuda' => 'Abre o prompt do assistente que responde perguntas sobre o conjunto de planejamentos.',
            'default' => 'Você é a IANNE, assistente pedagógica de uma escola municipal de Roraima. O coordenador/diretor vai fazer uma pergunta sobre o CONJUNTO de planejamentos abaixo (metodologias usadas, contagens, comparações, erros recorrentes, sugestões de formação etc.).',
        ],
        'assistente_extra' => [
            'grupo' => 'assistente',
            'label' => 'Regras adicionais do chat (opcional)',
            'ajuda' => 'Acrescentado à lista de regras. As regras de citar a fonte e de não inventar professores são fixas e continuam valendo.',
            'default' => '',
        ],

        // ---------- Inferência de metadados (upload) ----------
        'inferencia_disciplina' => [
            'grupo' => 'inferencia',
            'label' => 'Regra de disciplina',
            'ajuda' => 'Como a IANNE decide o componente curricular ao ler o documento. É a regra que mais varia entre redes.',
            'default' => "Os planejamentos desta rede são QUINZENAIS.
- Se o documento abrange DOIS OU MAIS componentes curriculares (ex: Português, Matemática, História, Geografia, Ciências, Ensino Religioso e Arte no mesmo plano — típico de professor titular dos anos iniciais), retorne exatamente \"Polivalente\".\n- Se abrange UM único componente (ex: professor específico de Educação Física, Arte, Inglês), retorne o nome desse componente (ex: \"Educação Física\").",
        ],
        'inferencia_tipo' => [
            'grupo' => 'inferencia',
            'label' => 'Regra de tipo de documento',
            'ajuda' => 'Como a IANNE decide se o arquivo é planejamento, relatório ou outro. É esta decisão que define QUAL rubrica será aplicada na correção — errar aqui faz o relatório ser cobrado como plano.',
            'default' => "- \"planejamento\": documento PROSPECTIVO, que organiza aulas futuras. Sinais: datas de aulas a ministrar, objetivos, metodologia, recursos, avaliação prevista, tabela por dia/semana, habilidades a desenvolver.\n"
                . "- \"relatorio\": documento RETROSPECTIVO, que descreve o que JÁ aconteceu, normalmente sobre UM aluno nomeado. Sinais: nome de um estudante, descrição de desempenho e comportamento no período, frequência, parecer conclusivo, menção a AEE, laudo, monitor, tecnologia assistiva, adaptação. Relatório de educação infantil também entra aqui.\n"
                . "- \"outro\": não se encaixa com clareza em nenhum dos dois.\n"
                . "Na dúvida entre planejamento e relatório, pergunte-se: o texto PLANEJA o que vai ser feito, ou RELATA o que foi feito e como o aluno respondeu? Relatar = \"relatorio\".",
        ],
        'inferencia_titulo' => [
            'grupo' => 'inferencia',
            'label' => 'Regra de título',
            'ajuda' => 'Como o título do documento é montado a partir do que a IANNE encontra no arquivo.',
            'default' => "- Construa a partir das DATAS DE APLICAÇÃO do plano, extraídas do próprio documento, no formato: \"<Tipo> <datas> — <turma>\". Ex: \"Planejamento 12/05 a 23/05 — 3º Ano B\".\n- Se o documento não trouxer as datas, use o período de referência (ex: \"Planejamento 1º Bimestre — 3º Ano B\"); em último caso, um título descritivo curto.",
        ],
    ];

    /**
     * Grupos exibidos na tela, na ordem.
     */
    public const GROUPS = [
        'parecer' => [
            'titulo' => 'Parecer do planejamento',
            'descricao' => 'O parecer que o coordenador recebe ao clicar em "Analisar". É o que gera valor no dia a dia.',
            'fixo' => 'Continuam travados no código: a proibição de afirmar que uma habilidade "não existe no DCRR" (o material enviado é sempre um recorte), a obrigação de sugerir a habilidade correta ao apontar erro, a regra de que fim de semana não é dia letivo salvo observação do coordenador, e o formato das cinco seções do parecer.',
        ],
        'relatorio' => [
            'titulo' => 'Parecer de relatório',
            'descricao' => 'Usado quando o documento é classificado como relatório — acompanhamento de aluno, AEE, educação infantil. Régua própria: avalia o texto sobre o estudante, não uma estrutura de aulas.',
            'fixo' => 'Continuam travados: a seção de alerta de saúde vir ANTES de tudo quando houver menção a dor ou mobilidade, a obrigação de propor a reescrita de cada termo capacitista encontrado (e não só apontá-lo), a proibição de cobrar do relatório elementos de planejamento, e as mesmas regras de DCRR do parecer de planejamento.',
        ],
        'outro' => [
            'titulo' => 'Parecer de documento não classificado',
            'descricao' => 'Rede de segurança para o que não for planejamento nem relatório — um PEI, por exemplo, até ganhar régua própria.',
            'fixo' => 'Continua travado: nunca cobrar do documento elementos que ele não se propõe a ter. É justamente o erro que fazia relatório voltar "todo errado".',
        ],
        'assistente' => [
            'titulo' => 'IANNE Assistente (chat da escola)',
            'descricao' => 'O chat que responde perguntas sobre o conjunto de planejamentos da escola.',
            'fixo' => 'Continuam travados: responder somente com base nos documentos, identificar professor e documento ao citar um achado, listar nominalmente em contagens, e avisar quando a análise cobre só os documentos mais recentes.',
        ],
        'inferencia' => [
            'titulo' => 'Inferência de metadados (upload)',
            'descricao' => 'O que a IANNE identifica sozinha quando o coordenador arrasta um arquivo: professor, turma, disciplina e período.',
            'fixo' => 'Continuam travados: o formato JSON da resposta (mexer nele quebra a tela de correções) e as regras de comparação de nomes ignorando acentos e abreviações.',
        ],
    ];

    /**
     * @return array<string, string> chave => texto padrão
     */
    public static function defaults(): array
    {
        return array_map(fn ($b) => $b['default'], self::BLOCKS);
    }

    /**
     * Blocos em vigor para um município: padrão sobrescrito pelo que foi salvo.
     * Bloco salvo em branco volta ao padrão — exceto os opcionais, que podem
     * legitimamente ficar vazios.
     *
     * @return array<string, string>
     */
    public static function for(?int $tenantId): array
    {
        $blocos = self::defaults();

        if ($tenantId === null) {
            return $blocos;
        }

        $salvos = TenantAiPrompt::where('tenant_id', $tenantId)->first()?->blocks ?? [];

        foreach ($blocos as $chave => $padrao) {
            $valor = trim((string) ($salvos[$chave] ?? ''));

            if ($valor !== '') {
                $blocos[$chave] = $valor;
            }
        }

        return $blocos;
    }

    /**
     * @return array<string, array> blocos de um grupo, preservando as chaves
     */
    public static function blocksOfGroup(string $grupo): array
    {
        return array_filter(self::BLOCKS, fn ($b) => $b['grupo'] === $grupo);
    }
}
