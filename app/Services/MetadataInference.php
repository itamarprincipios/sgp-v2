<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Infere metadados (professor, turma, disciplina, período, tipo, título) do texto
 * de um documento subido pelo coordenador, casando com os cadastros da escola.
 * Pivô "Plataforma de Correção" — AGENTS.md seção 14.
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
     * @return array{professor_id: ?int, class_id: ?int, discipline: ?string, reference_label: ?string, type: string, title: ?string, professor_name_detected: ?string, class_name_detected: ?string}
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

        $prompt = <<<PROMPT
Você analisa documentos pedagógicos (planejamentos de aula, relatórios) de escolas municipais brasileiras.
Extraia os metadados do documento abaixo e retorne APENAS um objeto JSON com estes campos:

{
  "professor_id": <id numérico do professor na lista abaixo cujo nome aparece no documento, ou null se nenhum casar>,
  "professor_name_detected": <nome do professor como está escrito no documento, ou null>,
  "class_id": <id numérico da turma na lista abaixo mencionada no documento, ou null>,
  "class_name_detected": <turma como está escrita no documento (ex: "3º Ano B"), ou null>,
  "discipline": <componente curricular, seguindo a regra de disciplina abaixo>,
  "reference_label": <período de APLICAÇÃO do plano normalizado (ex: "Quinzena 12/05 a 23/05/{$currentYear}", "1º Bimestre {$currentYear}"), ou null>,
  "type": <"planejamento", "relatorio" ou "outro">,
  "title": <título seguindo a regra de título abaixo (máx 80 caracteres)>
}

Regra de disciplina (os planejamentos desta rede são QUINZENAIS):
- Se o documento abrange DOIS OU MAIS componentes curriculares (ex: Português, Matemática, História, Geografia, Ciências, Ensino Religioso e Arte no mesmo plano — típico de professor titular dos anos iniciais), retorne exatamente "Polivalente".
- Se abrange UM único componente (ex: professor específico de Educação Física, Arte, Inglês), retorne o nome desse componente (ex: "Educação Física").

Regra de título:
- Construa a partir das DATAS DE APLICAÇÃO do plano, extraídas do próprio documento, no formato: "<Tipo> <datas> — <turma>". Ex: "Planejamento 12/05 a 23/05 — 3º Ano B".
- Se o documento não trouxer as datas, use o período de referência (ex: "Planejamento 1º Bimestre — 3º Ano B"); em último caso, um título descritivo curto.

Regras de matching:
- Compare nomes ignorando acentos, maiúsculas e nomes parciais (ex: "Profª Maria S." casa com "Maria da Silva").
- Só preencha professor_id/class_id se tiver confiança razoável; na dúvida, use null e preencha apenas o campo *_detected.
- Se o documento não citar o ano do período, assuma {$currentYear}.

PROFESSORES CADASTRADOS:
{$professorList}

TURMAS CADASTRADAS:
{$classList}

DOCUMENTO:
{$excerpt}
PROMPT;

        $defaults = [
            'professor_id' => null,
            'professor_name_detected' => null,
            'class_id' => null,
            'class_name_detected' => null,
            'discipline' => null,
            'reference_label' => null,
            'type' => 'planejamento',
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

            if (!in_array($result['type'], ['planejamento', 'relatorio', 'outro'], true)) {
                $result['type'] = 'outro';
            }

            return $result;
        } catch (Exception $e) {
            Log::error('MetadataInference: falha na inferência — ' . $e->getMessage());
            return array_merge($defaults, ['error' => $e->getMessage()]);
        }
    }
}
