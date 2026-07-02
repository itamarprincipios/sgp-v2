<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use App\Models\Document;
use App\Models\SchoolClass;
use App\Models\Period;
use Exception;

class ContextBuilder
{
    /**
     * Recupera contexto completo de uma escola.
     */
    public function getSchoolContext(int $schoolId): array
    {
        $school = School::findOrFail($schoolId);

        // Planejamentos da escola
        $documents = Document::whereHas('user', function ($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->orderBy('id', 'desc')->get();

        // Professores da escola
        $professors = User::where('school_id', $schoolId)
            ->where('role', 'professor')
            ->get();

        // Coordenadores da escola
        $coordinators = User::where('school_id', $schoolId)
            ->where('role', 'coordinator')
            ->get();

        return [
            'tipo' => 'escola',
            'escola' => [
                'nome' => $school->name,
                'endereco' => $school->address ?? 'Não informado',
                'codigo_inep' => $school->inep_code ?? 'N/A',
                'diretor' => $school->director->name ?? 'Não informado',
                'telefone_diretor' => $school->director->whatsapp ?? 'Não informado'
            ],
            'coordenadores' => $this->extractCoordinatorsInfo($coordinators),
            'estatisticas' => [
                'total_professores' => $professors->count(),
                'total_coordenadores' => $coordinators->count(),
                'total_planejamentos' => $documents->count(),
                'planejamentos_enviados' => $documents->where('status', 'enviado')->count(),
                'planejamentos_aprovados' => $documents->where('status', 'aprovado')->count(),
                'planejamentos_rejeitados' => $documents->where('status', 'rejeitado')->count()
            ],
            'professores' => $this->extractProfessorsInfo($professors),
            'planejamentos_recentes' => $this->extractPlanningsInfo($documents->take(10)->all())
        ];
    }

    /**
     * Recupera contexto de um professor específico.
     */
    public function getProfessorContext(int $professorId, ?int $periodId = null): array
    {
        $professor = User::with(['school', 'schoolClass'])->findOrFail($professorId);

        $query = Document::where('user_id', $professorId);
        if ($periodId) {
            $query->where('period_id', $periodId);
        }
        $documents = $query->orderBy('id', 'desc')->get();

        return [
            'tipo' => 'professor',
            'professor' => [
                'nome' => $professor->name,
                'email' => $professor->email,
                'whatsapp' => $professor->whatsapp ?? 'Não informado',
                'turma' => $professor->schoolClass->name ?? 'N/A',
                'escola' => $professor->school->name ?? 'N/A',
                'educacao_fisica' => $professor->is_physical_education ? 'Sim' : 'Não'
            ],
            'estatisticas' => [
                'total_planejamentos' => $documents->count(),
                'enviados' => $documents->where('status', 'enviado')->count(),
                'aprovados' => $documents->where('status', 'aprovado')->count(),
                'rejeitados' => $documents->where('status', 'rejeitado')->count()
            ],
            'planejamentos' => $this->extractPlanningsInfo($documents->all())
        ];
    }

    /**
     * Recupera contexto de uma turma específica.
     */
    public function getClassContext(int $classId): array
    {
        $class = SchoolClass::with('school')->findOrFail($classId);

        $professors = User::where(function($q) use ($classId) {
            $q->where('class_id', $classId)
              ->orWhere('monitor_class_id', $classId);
        })
        ->where('role', 'professor')
        ->get();

        $documents = Document::whereIn('user_id', $professors->pluck('id'))->get();

        return [
            'tipo' => 'turma',
            'turma' => [
                'nome' => $class->name,
                'escola' => $class->school->name ?? 'N/A'
            ],
            'professores' => $this->extractProfessorsInfo($professors),
            'planejamentos' => $this->extractPlanningsInfo($documents->all())
        ];
    }

    /**
     * Recupera contexto agregado de múltiplas escolas.
     */
    public function getMultiSchoolContext(array $schoolIds): array
    {
        $schools = School::whereIn('id', $schoolIds)->get();

        $professors = User::with('school')
            ->whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->get();

        $coordinators = User::with('school')
            ->whereIn('school_id', $schoolIds)
            ->where('role', 'coordinator')
            ->get();

        $documents = Document::whereHas('user', function ($query) use ($schoolIds) {
            $query->whereIn('school_id', $schoolIds);
        })->orderBy('id', 'desc')->get();

        return [
            'tipo' => 'multi_escola',
            'descricao' => 'Contexto agregado de ' . $schools->count() . ' escola(s)',
            'escolas' => $this->extractSchoolsInfo($schools),
            'estatisticas' => [
                'total_escolas' => $schools->count(),
                'total_professores' => $professors->count(),
                'total_coordenadores' => $coordinators->count(),
                'total_planejamentos' => $documents->count(),
                'planejamentos_enviados' => $documents->where('status', 'enviado')->count(),
                'planejamentos_aprovados' => $documents->where('status', 'aprovado')->count(),
                'planejamentos_rejeitados' => $documents->where('status', 'rejeitado')->count()
            ],
            'professores' => $this->extractProfessorsInfo($professors),
            'coordenadores' => $this->extractCoordinatorsInfo($coordinators),
            'planejamentos_recentes' => $this->extractPlanningsInfo($documents->take(20)->all())
        ];
    }

    /**
     * Recupera contexto global da rede municipal.
     */
    public function getNetworkContext(): array
    {
        $schools = School::all();
        $professors = User::with('school')->where('role', 'professor')->get();
        $coordinators = User::with('school')->where('role', 'coordinator')->get();
        $documents = Document::all();
        $periods = Period::all();

        // Turmas vagas (sem professor titular atribuído)
        $vacantClasses = SchoolClass::whereNotExists(function ($query) {
            $query->selectRaw(1)
                ->from('users')
                ->whereRaw('users.class_id = classes.id')
                ->where('users.role', 'professor')
                ->where('users.is_physical_education', false)
                ->where('users.is_monitor', false);
        })
        ->with('school')
        ->get();

        $vacantClassesFormatted = $vacantClasses->map(function ($c) {
            return [
                'escola' => $c->school->name ?? 'N/A',
                'turma' => $c->name
            ];
        })->values()->all();

        // Monitores
        $monitors = $professors->where('is_monitor', true);
        $monitorsFormatted = $monitors->map(function ($m) {
            return [
                'nome' => $m->name,
                'escola' => $m->school->name ?? 'N/A',
                'whatsapp' => $m->whatsapp ?? 'N/A'
            ];
        })->values()->all();

        return [
            'tipo' => 'rede_municipal',
            'estatisticas_globais' => [
                'total_escolas' => $schools->count(),
                'total_professores' => $professors->count(),
                'total_monitores' => $monitors->count(),
                'total_coordenadores' => $coordinators->count(),
                'total_planejamentos' => $documents->count(),
                'total_periodos' => $periods->count(),
                'turmas_sem_professor' => $vacantClasses->count()
            ],
            'turmas_vagas' => $vacantClassesFormatted,
            'monitores' => $monitorsFormatted,
            'escolas' => $this->extractSchoolsInfo($schools),
            'coordenadores' => $this->extractCoordinatorsInfo($coordinators),
            'professores_resumo' => $this->extractProfessorsInfo($professors->take(20))
        ];
    }

    /**
     * Recupera contexto de Educação Física.
     */
    public function getPhysicalEducationContext(): array
    {
        $professors = User::with('school')
            ->where('role', 'professor')
            ->where('is_physical_education', true)
            ->get();

        $plannings = Document::whereIn('user_id', $professors->pluck('id'))
            ->orderBy('id', 'desc')
            ->get();

        $totalProfessors = $professors->count();
        $enviados = $plannings->where('status', 'enviado')->count();

        return [
            'tipo' => 'rede_educacao_fisica',
            'descricao' => 'Contexto de Educação Física da rede municipal.',
            'estatisticas_edfis' => [
                'total_professores' => $totalProfessors,
                'total_planejamentos' => $plannings->count(),
                'enviados' => $enviados,
                'atrasados' => $plannings->where('status', 'atrasado')->count(),
                'aprovados' => $plannings->where('status', 'aprovado')->count(),
                'taxa_entrega' => $totalProfessors > 0 ? round(($enviados / $totalProfessors) * 100, 1) . '%' : '0%'
            ],
            'professores' => $this->extractProfessorsInfo($professors),
            'planejamentos_recentes' => $this->extractPlanningsInfo($plannings->take(20)->all())
        ];
    }

    private function extractProfessorsInfo($professors): array
    {
        $list = is_array($professors) ? $professors : $professors->all();
        return array_map(function ($prof) {
            $profData = is_array($prof) ? $prof : $prof->toArray();
            return [
                'nome' => $profData['name'],
                'email' => $profData['email'] ?? 'Não informado',
                'whatsapp' => $profData['whatsapp'] ?? 'Não informado',
                'escola' => $prof instanceof User && $prof->school ? $prof->school->name : ($profData['school_name'] ?? 'N/A'),
                'turma' => $prof instanceof User && $prof->schoolClass ? $prof->schoolClass->name : ($profData['class_name'] ?? 'N/A'),
                'educacao_fisica' => !empty($profData['is_physical_education']) ? 'Sim' : 'Não'
            ];
        }, $list);
    }

    private function extractCoordinatorsInfo($coordinators): array
    {
        $list = is_array($coordinators) ? $coordinators : $coordinators->all();
        return array_map(function ($coord) {
            $coordData = is_array($coord) ? $coord : $coord->toArray();
            return [
                'nome' => $coordData['name'],
                'email' => $coordData['email'] ?? 'Não informado',
                'whatsapp' => $coordData['whatsapp'] ?? 'Não informado',
                'escola' => $coord instanceof User && $coord->school ? $coord->school->name : ($coordData['school_name'] ?? 'N/A')
            ];
        }, $list);
    }

    private function extractSchoolsInfo($schools): array
    {
        $list = is_array($schools) ? $schools : $schools->all();
        return array_map(function ($school) {
            $schoolData = is_array($school) ? $school : $school->toArray();
            $director = $school instanceof School ? $school->director : null;
            return [
                'nome' => $schoolData['name'],
                'endereco' => $schoolData['address'] ?? 'Não informado',
                'codigo_inep' => $schoolData['inep_code'] ?? 'N/A',
                'diretor' => $director->name ?? 'Não informado',
                'telefone_diretor' => $director->whatsapp ?? 'Não informado'
            ];
        }, $list);
    }

    private function extractPlanningsInfo(array $documents): array
    {
        return array_map(function ($doc) {
            $info = [
                'titulo' => $doc->title,
                'periodo' => $doc->period->name ?? 'N/A',
                'professor' => $doc->user->name ?? 'N/A',
                'status' => $doc->status,
                'data_envio' => $doc->submitted_at ? $doc->submitted_at->format('d/m/Y') : ''
            ];

            if (!empty($doc->content_text)) {
                $content = mb_substr($doc->content_text, 0, 1500);
                if (mb_strlen($doc->content_text) > 1500) {
                    $content .= '... [conteúdo truncado]';
                }
                $info['conteudo'] = $content;
            }

            return $info;
        }, $documents);
    }
}
