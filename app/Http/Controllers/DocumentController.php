<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Period;
use App\Services\DocumentExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    protected $extractor;

    public function __construct(DocumentExtractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * Store a newly created document in storage (upload planning).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Validate the request
        $request->validate([
            'file' => ['required', 'file', 'mimes:docx', 'max:10240'], // only .docx up to 10MB
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:planejamento,relatorio'],
            'period_id' => ['required', 'exists:periods,id'],
        ], [
            'file.mimes' => 'Formato de arquivo não suportado. Apenas arquivos Word (.docx) são permitidos para extração e análise da IANNE.',
        ]);

        $period = Period::findOrFail($request->period_id);

        // Period must be global (no school_id) or belong to the professor's own school
        if ($period->school_id && $period->school_id != $user->school_id) {
            abort(403, 'Este cronograma não pertence à sua escola.');
        }

        // Check if there is an existing document for this period/user to replace it
        $existingDoc = Document::where('user_id', $user->id)
            ->where('period_id', $request->period_id)
            ->first();

        $wasReplaced = false;

        if ($existingDoc) {
            // Delete old physical file
            $oldPath = public_path('uploads/' . $existingDoc->file_path);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
            // Delete database record
            $existingDoc->delete();
            $wasReplaced = true;
        }

        // Calculate score logic
        $score_base = 10.00;
        $penalty_delay = 0.00;

        $now = now();
        $deadline = $period->deadline;

        if ($now->lte($deadline)) {
            // Submitted within/before deadline
            // T_total = 7 days in minutes = 10080
            $T_total = 10080;
            $diff_minutes = $now->diffInMinutes($deadline, false);
            $T_restante = max(0, min($T_total, $diff_minutes));

            $score_base = 10.00 + ($T_restante / $T_total) * 10.00;
        } else {
            // Late submission
            $score_base = 10.00;
            $days_delay = $now->diffInDays($deadline);

            if ($days_delay <= 1) {
                $penalty_delay = 2.00;
            } elseif ($days_delay <= 2) {
                $penalty_delay = 5.00;
            } else {
                $penalty_delay = 10.00;
            }
        }

        $score_final = max(0.00, $score_base - $penalty_delay);

        // Upload new file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $fileName);

        // Create the document
        $document = Document::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'period_id' => $request->period_id,
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $fileName,
            'status' => $penalty_delay > 0 ? 'atrasado' : 'enviado',
            'score_base' => $score_base,
            'penalty_delay' => $penalty_delay,
            'score_final' => $score_final,
            'submitted_at' => now(),
        ]);

        // Trigger automatic text extraction
        try {
            $this->extractor->extractAndSave($document->id);
        } catch (\Exception $e) {
            logger()->error("Erro na extração automática do documento {$document->id}: " . $e->getMessage());
        }

        $successMsg = $wasReplaced 
            ? 'Documento anterior substituído com sucesso!' 
            : 'Documento enviado com sucesso!';

        return redirect()->route('professor.dashboard')->with('success', $successMsg);
    }
}
