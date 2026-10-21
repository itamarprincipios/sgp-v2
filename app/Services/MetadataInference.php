<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Infere metadados (TIPO do documento, professor, turma, aluno, disciplina,
 * período, título) do texto de um documento subido pelo coordenador, casando
 * com os cadastros da escola. Pivô "Plataforma de Correção" — AGENTS.md seção 14.
 *
 * O campo de envio é um só: o coordenador arrasta planejamento, PEI ou
 * relatório sem escolher nada antes, e é aqui que se decide o que o arquivo é
 * (catálogo em DocumentKinds). O tipo manda no resto — nos campos que o card
 * pede e na rubrica do parecer —, então ele é validado contra o catálogo e
 * nunca aceito cru da IA.
 */
class MetadataInference
{
    private AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Analisa o texto extraído e retorna os metadados inferidos.
     *
     * @param string $contentText Texto extraído do documento
     * @param array $schoolIds Escolas do coordenador (para matching de cadastros)
     * @return array{doc_kind: string, type: string, professor_id: ?int, class_id: ?int, discipline: ?string, student_name: ?string, reference_label: ?string, title: ?string, professor_name_detected: ?string, class_name_detected: ?string, error: ?string}
     */
    public function infer(string $contentText, array $schoolIds): array
    {
        $professors = User::whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->get(['id', 'name', 'class_id']);

        $classes = SchoolClass::whereIn('school_id', $schoolIds)
            ->get(['id', 'name']);

        $professorList = $professors->map(fn ($p) => "{$p->id}: {$p->name}")->implode("\n");
        $classList = $classes->map(fn ($c) => "{$c->id}: {$c->name}")->implode("\n");

        $excerpt = mb_substr($contentText, 0, 4000);
        $currentYear = now()->year;
        $kindList = DocumentKinds::promptList();

        // Blocos editáveis pelo SuperAdmin (tela "Prompts da IANNE" do município).
        $b = PromptSettings::for(auth()->user()?->tenant_id);

        $prompt = <<<PROMPT
Você analisa documentos pedagógicos de escolas municipais brasileiras — planejamentos de aula, PEIs e relatórios.
Extraia os metadados do documento abaixo e retorne APENAS um objeto JSON com estes campos:

{
  "doc_kind": <tipo do documento, uma das chaves da lista de TIPOS abaixo>,
  "professor_id": <id numérico do professor na lista abaixo cujo nome aparece no documento, ou null se nenhum casar>,
  "professor_name_detected": <nome do professor como está escrito no documento, ou null>,
  "class_id": <id numérico da turma na lista abaixo mencionada no documento, ou null>,
  "class_name_detected": <turma como está escrita no documento (ex: "3º Ano B"), ou null>,
  "student_name": <nome do aluno/criança de quem o documento trata, quando ele for individual (PEI e relatórios individuais); null nos demais tipos>,
  "discipline": <componente curricular, seguindo a regra de disciplina abaixo>,
  "reference_label": <período a que o documento se refere, normalizado (ex: "Quinzena 12/05 a 23/05/{$currentYear}", "1º Bimestre {$currentYear}"), ou null>,
  "title": <título seguindo a regra de título abaixo (máx 80 caracteres)>
}

TIPOS DE DOCUMENTO (escolha exatamente uma chave):
{$kindList}

Como decidir o tipo:
- Documento que PLANEJA o que será feito com a turma (datas futuras, objetivos, conteúdos, metodologia, avaliação) = "planejamento".
- Documento que PLANEJA metas e adaptações de UM aluno nomeado = "pei".
- Documento que RELATA o que já aconteceu com a TURMA num período = "relatorio_professor".
- Documento que RELATA o percurso de UM aluno público da educação especial = "relatorio_aluno_especial".
- Documento que RELATA o desenvolvimento de UMA criança da educação infantil (campos de experiência, brincadeiras, sem notas) = "relatorio_educacao_infantil".
- Na dúvida entre dois tipos, escolha o mais específico que o texto sustentar; o coordenador confirma depois.

Regra de disciplina (vale para planejamento; nos relatórios e no PEI, preencha só se o documento indicar o componente, senão null):
{$b['inferencia_disciplina']}

Regra de título:
{$b['inferencia_titulo']}
- Se o documento NÃO for planejamento, use o formato "<tipo> — <aluno ou turma> — <período>" (ex: "PEI — João Pedro — 2026", "Relatório 2º Bimestre — 4º Ano A").

Regras de matching:
- Compare nomes ignorando acentos, maiúsculas e nomes parciais (ex: "Profª Maria S." casa com "Maria da Silva").
- Só preencha professor_id/class_id se tiver confiança razoável; na dúvida, use null e preencha apenas o campo *_detected.
- student_name é o nome do ALUNO (nunca o do professor) e sai do próprio documento — não há cadastro de alunos para casar.
- Se o documento não citar o ano do período, assuma {$currentYear}.

PROFESSORES CADASTRADOS:
{$professorList}

TURMAS CADASTRADAS:
{$classList}

DOCUMENTO:
{$excerpt}
PROMPT;

        $defaults = [
            'doc_kind' => DocumentKinds::DEFAULT,
            'type' => DocumentKinds::legacyType(DocumentKinds::DEFAULT),
            'professor_id' => null,
            'professor_name_detected' => null,
            'class_id' => null,
            'class_name_detected' => null,
            'student_name' => null,
            'discipline' => null,
            'reference_label' => null,
            'title' => null,
            'error' => null,
        ];

        try {
            $response = $this->ai->query($prompt, true);
            $data = json_decode($response, true);

            if (!is_array($data)) {
                Log::warning('MetadataInference: resposta não é JSON válido', ['response' => mb_substr($response, 0, 500)]);
                return array_merge($defaults, ['error' => 'A IANNE retornou uma resposta em formato inválido.']);
            }

            $result = array_merge($defaults, array_intersect_key($data, $defaults));

            // Valida ids contra os cadastros reais (a IA pode alucinar ids)
            if ($result['professor_id'] && !$professors->contains('id', (int) $result['professor_id'])) {
                $result['professor_id'] = null;
            }
            if ($result['class_id'] && !$classes->contains('id', (int) $result['class_id'])) {
                $result['class_id'] = null;
            }

            // Tipo fora do catálogo vira "outro" — melhor o coordenador escolher
            // do que a tela receber uma chave que ninguém sabe corrigir.
            if (!DocumentKinds::exists($result['doc_kind'])) {
                if (!empty($result['doc_kind'])) {
                    Log::warning('MetadataInference: doc_kind fora do catálogo', ['doc_kind' => $result['doc_kind']]);
                }
                $result['doc_kind'] = 'outro';
            }

            // O balde antigo (documents.type) é derivado, nunca pedido à IA.
            $result['type'] = DocumentKinds::legacyType($result['doc_kind']);

            // Aluno só faz sentido nos tipos individuais; nos outros, limpa para
            // não poluir o card com o nome de alguém citado de passagem.
            if (!DocumentKinds::get($result['doc_kind'])['pede_aluno']) {
                $result['student_name'] = null;
            } elseif (!empty($result['student_name'])) {
                $result['student_name'] = mb_substr(trim((string) $result['student_name']), 0, 255);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('MetadataInference: falha na inferência — ' . $e->getMessage());
            return array_merge($defaults, ['error' => $e->getMessage()]);
        }
    }
}
