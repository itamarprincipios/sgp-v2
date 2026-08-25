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
