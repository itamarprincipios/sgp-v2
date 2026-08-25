<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\DocumentExtractor;
use App\Services\MetadataInference;
use App\Services\PlanAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Fluxo de correção de planejamentos — pivô "Plataforma de Correção" (AGENTS.md seção 14).
 * O coordenador arrasta os documentos que já recebeu (e-mail/WhatsApp), a IA infere
 * os metadados (professor, turma, disciplina, período) e ele apenas confirma.
 */
class CorrectionController extends Controller
{
    /**
     * Workspace de correções: fila de confirmação + documentos em correção.
     */
    public function index()
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $documents = Document::whereIn('school_id', $schoolIds)
            ->whereIn('status', ['aguardando_confirmacao', 'em_correcao', 'corrigido'])
            ->with(['user:id,name', 'schoolClass:id,name', 'uploadedBy:id,name'])
            ->orderByDesc('id')
            ->get();

        $professors = User::whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);

        $classes = SchoolClass::whereIn('school_id', $schoolIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('school.corrections', compact('documents', 'professors', 'classes'));
    }

    /**
     * Recebe um arquivo do drag-and-drop, extrai o texto, infere metadados via IA
     * e devolve JSON para o card de confirmação. Um request por arquivo.
     */
    public function store(Request $request, DocumentExtractor $extractor, MetadataInference $inference)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'file' => ['required', 'file', 'mimes:docx,pdf', 'max:10240'],
        ], [
            'file.mimes' => 'Formato não suportado. Envie arquivos Word (.docx) ou PDF.',
            'file.max' => 'Arquivo muito grande (máximo 10MB).',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $file->move(public_path('uploads'), $fileName);

        $document = Document::create([
            'tenant_id' => $user->tenant_id,
            'uploaded_by' => $user->id,
            'school_id' => $user->school_id ?? ($schoolIds[0] ?? null),
            'title' => pathinfo($originalName, PATHINFO_FILENAME),
            'type' => 'planejamento',
            'file_path' => $fileName,
            'status' => 'aguardando_confirmacao',
            'submitted_at' => now(),
        ]);

        // Extração de texto (docx local, PDF via Gemini multimodal)
        $extraction = $extractor->extractAndSave($document->id);
        $document->refresh();

        // Inferência de metadados via IA (só se houver texto)
        $inferred = null;
        if ($extraction['success'] && !empty($document->content_text)) {
            $inferred = $inference->infer($document->content_text, $schoolIds);

            $updates = array_filter([
                'user_id' => $inferred['professor_id'],
                'class_id' => $inferred['class_id'],
                'discipline' => $inferred['discipline'],
                'reference_label' => $inferred['reference_label'],
                'type' => $inferred['type'],
            ]);
            if (!empty($inferred['title'])) {
                $updates['title'] = mb_substr($inferred['title'], 0, 255);
            }
            // Se o professor foi identificado, o documento pertence à escola dele
            if ($inferred['professor_id']) {
                $professorSchool = User::find($inferred['professor_id'])?->school_id;
                if ($professorSchool && in_array($professorSchool, $schoolIds)) {
                    $updates['school_id'] = $professorSchool;
                }
            }
            $document->update($updates);
            $document->refresh();
        }

        // Diagnóstico para o coordenador: distingue "IA indisponível" de "não identifiquei".
        $aiError = null;
        if (!$extraction['success'] || empty($document->content_text)) {
            $aiError = 'Não consegui extrair o texto do arquivo. Se for um PDF digitalizado (imagem) ou protegido, a leitura automática não funciona — preencha os campos manualmente.';
        } elseif ($inferred && !empty($inferred['error'])) {
            $aiError = 'A IANNE está indisponível no momento (' . $inferred['error'] . '). Preencha os campos manualmente.';
        }

        return response()->json([
            'success' => true,
            'extraction_ok' => $extraction['success'],
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'type' => $document->type,
                'file_name' => $originalName,
                'professor_id' => $document->user_id,
                'class_id' => $document->class_id,
                'discipline' => $document->discipline,
                'reference_label' => $document->reference_label,
                'professor_name_detected' => $inferred['professor_name_detected'] ?? null,
                'class_name_detected' => $inferred['class_name_detected'] ?? null,
                'ai_error' => $aiError,
            ],
        ]);
    }

    /**
     * Coordenador confirma (ou corrige) os metadados inferidos pela IA.
     */
    public function confirm(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:documents,id'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:planejamento,relatorio,outro'],
            'professor_id' => ['nullable', 'exists:users,id'],
            'new_professor_name' => ['nullable', 'string', 'max:255'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'discipline' => ['nullable', 'string', 'max:100'],
            'reference_label' => ['nullable', 'string', 'max:100'],
        ]);

        $document = Document::findOrFail($request->id);

        if (!in_array($document->school_id, $schoolIds)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $schoolId = $document->school_id;
        $professorId = $request->professor_id;

        if ($professorId) {
            $professor = User::findOrFail($professorId);
            if (!in_array($professor->school_id, $schoolIds)) {
                return response()->json(['success' => false, 'message' => 'Professor de outra escola.'], 422);
            }
            $schoolId = $professor->school_id;
        } elseif ($request->filled('new_professor_name')) {
            $name = trim(preg_replace('/\s+/', ' ', $request->new_professor_name));

            // Reaproveita cadastro existente com o mesmo nome na(s) escola(s) do
            // coordenador (a collation *_ci do MySQL ignora maiúsculas/acentos),
            // senão cada confirmação de um lote criaria um professor duplicado.
            $professor = User::whereIn('school_id', $schoolIds)
                ->where('role', 'professor')
                ->where('name', $name)
                ->orderBy('id')
                ->first();

            if (!$professor) {
                // Cadastro leve do professor a partir do que a IA extraiu do documento
                // (sem e-mail/login). Do próximo upload em diante ele casa sozinho.
                $professor = User::create([
                    'tenant_id' => $document->tenant_id,
                    'school_id' => $schoolId,
                    'name' => $name,
                    'email' => null,
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'professor',
                    'class_id' => $request->class_id,
                ]);
            } elseif (!$professor->class_id && $request->class_id) {
                $professor->update(['class_id' => $request->class_id]);
            }

            $professorId = $professor->id;
            $schoolId = $professor->school_id ?? $schoolId;
        }

        $document->update([
            'title' => $request->title,
            'type' => $request->type,
            'user_id' => $professorId,
            'class_id' => $request->class_id,
            'discipline' => $request->discipline,
            'reference_label' => $request->reference_label,
            'school_id' => $schoolId,
            'status' => 'em_correcao',
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Gera o parecer da IANNE para um documento (Fase 2 — correção assistida).
     * Recebe as respostas da caixa de diálogo (vigência, nº de aulas, observações).
     */
    public function analyze(Request $request, PlanAnalyzer $analyzer)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:documents,id'],
            'vigencia' => ['nullable', 'string', 'max:100'],
            'aulas' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        $document = Document::with(['user:id,name', 'schoolClass:id,name'])->findOrFail($request->id);

        if (!in_array($document->school_id, $schoolIds)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }
        if (empty($document->content_text)) {
            return response()->json(['success' => false, 'message' => 'Este documento não tem texto extraído para analisar.'], 422);
        }

        try {
            $analysis = $analyzer->analyze($document, [
                'vigencia' => $request->vigencia,
                'aulas' => $request->aulas,
                'observacoes' => $request->observacoes,
                'dcrr' => app(\App\Services\DcrrLibrary::class)->excerptFor(
                    $document->discipline,
                    $document->schoolClass->name ?? null
                ),
                'referencias' => app(\App\Services\ReferenceLibrary::class)->textFor($document->tenant_id),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'A IANNE não conseguiu analisar agora: ' . $e->getMessage(),
            ], 502);
        }

        $document->update(['analysis' => $analysis, 'analyzed_at' => now()]);

        return response()->json(['success' => true, 'analysis' => $analysis]);
    }

    /**
     * Salva o parecer editado pelo coordenador.
     */
    public function saveAnalysis(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:documents,id'],
            'analysis' => ['required', 'string'],
        ]);

        $document = Document::findOrFail($request->id);

        if (!in_array($document->school_id, $schoolIds)) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $document->update(['analysis' => $request->analysis, 'analyzed_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Aprova o documento: sai da Central de Correção e passa a compor o
     * histórico do professor (com o parecer disponível para releitura).
     */
    public function approve(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate(['id' => ['required', 'exists:documents,id']]);

        $document = Document::findOrFail($request->id);

        if (!in_array($document->school_id, $schoolIds)
            || !in_array($document->status, ['em_correcao', 'corrigido'])) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $document->update(['status' => 'aprovado']);

        return response()->json(['success' => true]);
    }

    /**
     * Remove um documento do fluxo de correção (arquivo físico + registro).
     */
    public function destroy(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate(['id' => ['required', 'exists:documents,id']]);

        $document = Document::findOrFail($request->id);

        if (!in_array($document->school_id, $schoolIds)
            || !in_array($document->status, ['aguardando_confirmacao', 'em_correcao'])) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        $path = public_path('uploads/' . $document->file_path);
        if (File::exists($path)) {
            File::delete($path);
        }
        $document->delete();

        return response()->json(['success' => true]);
    }
}
