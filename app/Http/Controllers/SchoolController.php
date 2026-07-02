<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Period;
use App\Models\Document;
use App\Support\TempPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class SchoolController extends Controller
{
    /**
     * List school plannings.
     */
    public function plannings()
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $schools = School::whereIn('id', $schoolIds)->get();
        $plannings = Period::whereIn('school_id', $schoolIds)
            ->orWhereNull('school_id')
            ->orderBy('id', 'desc')
            ->get();
            
        $showSchool = count($schoolIds) > 1;

        return view('school.plannings', compact('plannings', 'showSchool', 'schools'));
    }

    /**
     * Create planning form.
     */
    public function createPlanning()
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        $schools = School::whereIn('id', $schoolIds)->get();
        return view('school.planning_create', compact('schools'));
    }

    /**
     * Store planning.
     */
    public function storePlanning(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
        ]);
        
        if (!in_array($request->school_id, $schoolIds)) {
            return redirect()->route('school.plannings')->with('error', 'Acesso negado para esta escola.');
        }
        
        $start_date = $request->start_date;
        $deadline = date('Y-m-d 23:59:59', strtotime($start_date . ' - 1 day'));
        $opening_date = date('Y-m-d 00:00:00', strtotime($start_date . ' - 7 days'));

        Period::create([
            'tenant_id' => $user->tenant_id,
            'school_id' => $request->school_id,
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $request->end_date ?? date('Y-m-d 23:59:59', strtotime($start_date . ' + 30 days')),
            'deadline' => $deadline,
            'opening_date' => $opening_date,
            'is_active' => true,
            'is_physical_education' => $request->has('is_physical_education'),
            'is_monitor' => $request->has('is_monitor'),
            'is_first_grade' => $request->has('is_first_grade'),
        ]);

        return redirect()->route('school.plannings')->with('success', 'Cronograma de planejamento cadastrado com sucesso!');
    }

    /**
     * View planning detail.
     */
    public function viewPlanning(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:periods,id'],
        ]);

        $planning = Period::findOrFail($request->id);

        // Security check
        if (!in_array($planning->school_id, $schoolIds) && !empty($planning->school_id)) {
            return redirect()->route('school.dashboard')->with('error', 'Acesso negado.');
        }

        $schoolId = $planning->school_id ?? $user->school_id;

        $isPE = $planning->is_physical_education;
        $isMonitor = $planning->is_monitor;
        $isFirstGrade = $planning->is_first_grade;

        if ($isPE) {
            $details = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('classes as c', 'u.class_id', '=', 'c.id')
                ->leftJoin('documents as d', function($join) use ($planning) {
                    $join->on('u.id', '=', 'd.user_id')
                         ->where('d.period_id', '=', $planning->id);
                })
                ->where('u.school_id', '=', $schoolId)
                ->where('u.role', '=', 'professor')
                ->where('u.is_physical_education', '=', 1)
                ->selectRaw('COALESCE(c.name, "Educação Física") as class_name, u.name as professor_name, u.whatsapp, d.status, d.submitted_at, d.file_path, d.id, d.content_text, d.feedback, d.score_final')
                ->orderBy('c.name')
                ->orderBy('u.name')
                ->get();
        } elseif ($isMonitor) {
            $details = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('classes as c', 'u.class_id', '=', 'c.id')
                ->leftJoin('documents as d', function($join) use ($planning) {
                    $join->on('u.id', '=', 'd.user_id')
                         ->where('d.period_id', '=', $planning->id);
                })
                ->where('u.school_id', '=', $schoolId)
                ->where('u.role', '=', 'professor')
                ->where('u.is_monitor', '=', 1)
                ->selectRaw('COALESCE(c.name, "Monitoria M.A.E") as class_name, u.name as professor_name, u.whatsapp, d.status, d.submitted_at, d.file_path, d.id, d.content_text, d.feedback, d.score_final')
                ->orderBy('c.name')
                ->orderBy('u.name')
                ->get();
        } elseif ($isFirstGrade) {
            $details = \Illuminate\Support\Facades\DB::table('users as u')
                ->leftJoin('classes as c', 'u.class_id', '=', 'c.id')
                ->leftJoin('documents as d', function($join) use ($planning) {
                    $join->on('u.id', '=', 'd.user_id')
                         ->where('d.period_id', '=', $planning->id);
                })
                ->where('u.school_id', '=', $schoolId)
                ->where('u.role', '=', 'professor')
                ->where('u.is_first_grade', '=', 1)
                ->selectRaw('COALESCE(c.name, "1º Ano") as class_name, u.name as professor_name, u.whatsapp, d.status, d.submitted_at, d.file_path, d.id, d.content_text, d.feedback, d.score_final')
                ->orderBy('c.name')
                ->orderBy('u.name')
                ->get();
        } else {
            $details = \Illuminate\Support\Facades\DB::table('classes as c')
                ->leftJoin('users as u', function($join) {
                    $join->on('c.id', '=', 'u.class_id')
                         ->where('u.role', '=', 'professor')
                         ->where('u.is_physical_education', '=', 0)
                         ->where('u.is_monitor', '=', 0)
                         ->where('u.is_first_grade', '=', 0);
                })
                ->leftJoin('documents as d', function($join) use ($planning) {
                    $join->on('u.id', '=', 'd.user_id')
                         ->where('d.period_id', '=', $planning->id);
                })
                ->where('c.school_id', '=', $schoolId)
                ->selectRaw('c.name as class_name, u.name as professor_name, u.whatsapp, d.status, d.submitted_at, d.file_path, d.id, d.content_text, d.feedback, d.score_final')
                ->orderBy('c.name')
                ->orderBy('u.name')
                ->get();
        }

        // Group by Class
        $groupedData = [];
        foreach ($details as $row) {
            $groupedData[$row->class_name][] = $row;
        }

        return view('school.planning_detail', compact('planning', 'groupedData'));
    }

    /**
     * Edit planning form.
     */
    public function editPlanning(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:periods,id'],
        ]);
        
        $planning = Period::findOrFail($request->id);
        
        if ($planning->school_id && !in_array($planning->school_id, $schoolIds)) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $schools = School::whereIn('id', $schoolIds)->get();
        
        return view('school.planning_edit', compact('planning', 'schools'));
    }

    /**
     * Update planning.
     */
    public function updatePlanning(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:periods,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
        ]);

        $planning = Period::findOrFail($request->id);

        if (!in_array($request->school_id, $schoolIds) || ($planning->school_id && !in_array($planning->school_id, $schoolIds))) {
            return redirect()->route('school.plannings')->with('error', 'Acesso negado.');
        }

        $start_date = $request->start_date;
        $deadline = date('Y-m-d 23:59:59', strtotime($start_date . ' - 1 day'));
        $opening_date = date('Y-m-d 00:00:00', strtotime($start_date . ' - 7 days'));

        $planning->update([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $start_date . ' 00:00:00',
            'end_date' => $request->end_date ?? date('Y-m-d 23:59:59', strtotime($start_date . ' + 30 days')),
            'deadline' => $deadline,
            'opening_date' => $opening_date,
            'is_physical_education' => $request->has('is_physical_education'),
            'is_monitor' => $request->has('is_monitor'),
            'is_first_grade' => $request->has('is_first_grade'),
        ]);

        return redirect()->route('school.plannings')->with('success', 'Cronograma de planejamento atualizado com sucesso!');
    }

    /**
     * Delete planning.
     */
    public function deletePlanning(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:periods,id'],
        ]);

        $planning = Period::findOrFail($request->id);

        if ($planning->school_id && !in_array($planning->school_id, $schoolIds)) {
            return redirect()->route('school.plannings')->with('error', 'Acesso negado.');
        }

        $planning->delete();

        return redirect()->route('school.plannings')->with('success', 'Cronograma de planejamento excluído com sucesso.');
    }

    /**
     * List school classes.
     */
    public function classes()
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $schools = School::whereIn('id', $schoolIds)->get();
        $classes = SchoolClass::whereIn('school_id', $schoolIds)
            ->with('users')
            ->orderBy('id', 'desc')
            ->get();
            
        return view('school.classes', compact('classes', 'schools'));
    }

    /**
     * Store school class.
     */
    public function storeClass(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);
        
        if (!in_array($request->school_id, $schoolIds)) {
            return redirect()->route('school.classes')->with('error', 'Acesso negado para esta escola.');
        }
        
        SchoolClass::create([
            'school_id' => $request->school_id,
            'name' => $request->name,
        ]);
        
        return redirect()->route('school.classes')->with('success', 'Turma cadastrada com sucesso!');
    }

    /**
     * Edit school class.
     */
    public function editClass(Request $request)
    {
        return view('school.class_edit');
    }

    /**
     * Update school class.
     */
    public function updateClass(Request $request)
    {
        return redirect()->route('school.classes');
    }

    /**
     * Delete school class.
     */
    public function deleteClass(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:classes,id'],
        ]);
        
        $class = SchoolClass::findOrFail($request->id);
        
        if (!in_array($class->school_id, $schoolIds)) {
            return redirect()->route('school.classes')->with('error', 'Acesso negado para esta ação.');
        }
        
        // Remove class links from teachers
        User::where('class_id', $class->id)->update(['class_id' => null]);
        User::where('monitor_class_id', $class->id)->update(['monitor_class_id' => null]);
        
        $class->delete();
        
        return redirect()->route('school.classes')->with('success', 'Turma excluída com sucesso.');
    }

    /**
     * List school professors.
     */
    public function professors()
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $schools = School::whereIn('id', $schoolIds)->get();
        $school = $schools->first();
        
        $classes = SchoolClass::whereIn('school_id', $schoolIds)->get();
        
        $professors = User::whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->with(['schoolClass', 'monitorClass'])
            ->get();
            
        return view('school.professors', compact('professors', 'classes', 'schools', 'school', 'user'));
    }

    /**
     * Store professor.
     */
    public function storeProfessor(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'monitor_class_id' => ['nullable', 'exists:classes,id'],
        ]);
        
        if (!in_array($request->school_id, $schoolIds)) {
            return redirect()->route('school.professors')->with('error', 'Acesso negado para esta escola.');
        }
        
        $tempPassword = TempPassword::generate();

        User::create([
            'tenant_id' => $user->tenant_id,
            'school_id' => $request->school_id,
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($tempPassword),
            'role' => 'professor',
            'class_id' => $request->class_id,
            'monitor_class_id' => $request->monitor_class_id,
            'is_physical_education' => $request->has('is_physical_education'),
            'is_monitor' => $request->has('is_monitor'),
            'is_first_grade' => $request->has('is_first_grade'),
        ]);

        return redirect()->route('school.professors')->with('success', "Professor cadastrado com sucesso! Senha inicial: {$tempPassword} (informe ao professor e oriente a troca no primeiro acesso).");
    }

    /**
     * Edit professor.
     */
    public function editProfessor(Request $request)
    {
        return view('school.professor_edit');
    }

    /**
     * Update professor.
     */
    public function updateProfessor(Request $request)
    {
        return redirect()->route('school.professors');
    }

    /**
     * Delete professor.
     */
    public function deleteProfessor(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);
        
        $prof = User::findOrFail($request->id);
        
        if (!in_array($prof->school_id, $schoolIds) || $prof->role !== 'professor') {
            return redirect()->route('school.professors')->with('error', 'Acesso negado para esta ação.');
        }
        
        $prof->delete();
        
        return redirect()->route('school.professors')->with('success', 'Professor excluído com sucesso.');
    }

    /**
     * Reset professor password.
     */
    public function resetProfessorPassword(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);
        
        $prof = User::findOrFail($request->id);
        
        if (!in_array($prof->school_id, $schoolIds) || $prof->role !== 'professor') {
            return redirect()->route('school.professors')->with('error', 'Acesso negado para esta ação.');
        }
        
        $tempPassword = TempPassword::generate();

        $prof->update([
            'password' => Hash::make($tempPassword)
        ]);

        return redirect()->route('school.professors')->with('success', "A senha do professor {$prof->name} foi redefinida para: {$tempPassword} (oriente a troca no primeiro acesso).");
    }

    /**
     * Review document (Approve/Reject).
     */
    public function reviewDocument(Request $request)
    {
        $user = auth()->user();
        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:documents,id'],
            'status' => ['required', 'in:aprovado,ajustado,rejeitado'],
            'feedback' => ['nullable', 'string'],
        ]);

        $doc = Document::findOrFail($request->id);

        // Security check
        if (!in_array($doc->user->school_id, $schoolIds)) {
            return redirect()->route('school.dashboard')->with('error', 'Acesso negado.');
        }

        $rejection_count = $doc->rejection_count;
        $penalty_resubmission = $doc->penalty_resubmission;

        if ($request->status === 'rejeitado') {
            $rejection_count++;
            if ($rejection_count == 2) {
                $penalty_resubmission = 2.00;
            } elseif ($rejection_count == 3) {
                $penalty_resubmission = 7.00;
            } elseif ($rejection_count >= 4) {
                $penalty_resubmission = 10.00;
            }

            $doc->update([
                'status' => 'rejeitado',
                'rejection_count' => $rejection_count,
                'rejected_at' => now(),
                'penalty_resubmission' => $penalty_resubmission,
                'feedback' => $request->feedback,
                'score_final' => max(0.00, $doc->score_base - $doc->penalty_delay - $penalty_resubmission),
            ]);

            $successMsg = 'Planejamento devolvido para correção!';
        } else {
            $status = ($request->status === 'ajustado') ? 'ajustado' : 'aprovado';
            
            $doc->update([
                'status' => $status,
                'feedback' => $request->feedback,
                'score_final' => max(0.00, $doc->score_base - $doc->penalty_delay - $doc->penalty_resubmission),
            ]);

            $successMsg = ($request->status === 'ajustado') 
                ? 'Planejamento aprovado com ajustes!' 
                : 'Planejamento aprovado com sucesso!';
        }

        return redirect()->route('school.planning.view', ['id' => $doc->period_id])
            ->with('success', $successMsg);
    }

    /**
     * Associate planning to bimester.
     */
    public function associateToBimester(Request $request)
    {
        return redirect()->route('school.dashboard');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('school.dashboard')->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Upload photo.
     */
    public function uploadPhoto(Request $request)
    {
        return redirect()->route('school.dashboard');
    }

    /**
     * Mark uploads as viewed.
     */
    public function markUploadsAsViewed(Request $request)
    {
        session(['last_viewed_uploads' => now()]);
        return response()->json(['status' => 'success']);
    }

    /**
     * Store coordinator (Director only).
     */
    public function storeCoordinator(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'vice_director'])) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ]);
        
        if (!in_array($request->school_id, $schoolIds)) {
            return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('error', 'Acesso negado para esta escola.');
        }
        
        $tempPassword = TempPassword::generate();

        $coordinator = User::create([
            'tenant_id' => $user->tenant_id,
            'school_id' => $request->school_id,
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
            'password' => Hash::make($tempPassword),
            'role' => 'coordinator',
        ]);

        // Link in pivot table user_schools
        \Illuminate\Support\Facades\DB::table('user_schools')->insert([
            'user_id' => $coordinator->id,
            'school_id' => $request->school_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('success', "Coordenador cadastrado com sucesso! Senha inicial: {$tempPassword} (informe ao coordenador e oriente a troca no primeiro acesso).");
    }

    /**
     * Edit coordinator (Director only).
     */
    public function editCoordinator(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'vice_director'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);

        $coordinator = User::findOrFail($request->id);

        if (!in_array($coordinator->school_id, $schoolIds) || $coordinator->role !== 'coordinator') {
            abort(403, 'Acesso negado.');
        }

        $schools = School::whereIn('id', $schoolIds)->get();

        return view('school.coordinator_edit', compact('coordinator', 'schools'));
    }

    /**
     * Update coordinator (Director only).
     */
    public function updateCoordinator(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'vice_director'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $schoolIds = $user->getAssignedSchoolIds();

        $request->validate([
            'id' => ['required', 'exists:users,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->id],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ]);

        $coordinator = User::findOrFail($request->id);

        if (!in_array($coordinator->school_id, $schoolIds) || !in_array($request->school_id, $schoolIds) || $coordinator->role !== 'coordinator') {
            return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('error', 'Acesso negado.');
        }

        $coordinator->update([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp' => $request->whatsapp,
        ]);

        // Update user_schools pivot table
        \Illuminate\Support\Facades\DB::table('user_schools')
            ->where('user_id', $coordinator->id)
            ->update([
                'school_id' => $request->school_id,
                'updated_at' => now(),
            ]);

        return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('success', 'Coordenador atualizado com sucesso!');
    }

    /**
     * Reset coordinator password (Director only).
     */
    public function resetCoordinatorPassword(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'vice_director'])) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);
        
        $coordinator = User::findOrFail($request->id);
        
        if (!in_array($coordinator->school_id, $schoolIds) || $coordinator->role !== 'coordinator') {
            return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('error', 'Acesso negado.');
        }
        
        $tempPassword = TempPassword::generate();

        $coordinator->update([
            'password' => Hash::make($tempPassword),
        ]);

        return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('success', "A senha do coordenador {$coordinator->name} foi redefinida para: {$tempPassword} (oriente a troca no primeiro acesso).");
    }

    /**
     * Delete coordinator (Director only).
     */
    public function deleteCoordinator(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'vice_director'])) {
            abort(403, 'Acesso não autorizado.');
        }
        
        $schoolIds = $user->getAssignedSchoolIds();
        
        $request->validate([
            'id' => ['required', 'exists:users,id'],
        ]);
        
        $coordinator = User::findOrFail($request->id);
        
        if (!in_array($coordinator->school_id, $schoolIds) || $coordinator->role !== 'coordinator') {
            return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('error', 'Acesso negado.');
        }
        
        // Delete pivot links
        \Illuminate\Support\Facades\DB::table('user_schools')->where('user_id', $coordinator->id)->delete();
        
        $coordinator->delete();
        
        return redirect()->route('school.dashboard', ['tab' => 'coordinators'])->with('success', 'Coordenador excluído com sucesso.');
    }

    /**
     * Reports page.
     */
    public function reports(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['director', 'semed'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $type = $request->input('type', 'submissions');
        $professorId = $request->input('professor_id');
        $period = $request->input('period', 'annual');

        $schoolIds = $user->getAssignedSchoolIds();
        $schoolId = $request->input('school_id', $schoolIds[0] ?? null);

        if ($schoolId && !in_array($schoolId, $schoolIds) && $user->role !== 'semed') {
            return redirect()->route('school.dashboard')->with('error', 'Acesso negado para esta escola.');
        }

        $schools = School::whereIn('id', $schoolIds)->get();
        $professors = $schoolId ? User::where('school_id', $schoolId)->where('role', 'professor')->get() : [];

        $data = [];

        // Detect SQLite database driver
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $daysLateExpr = $isSqlite 
            ? "CAST((strftime('%s', 'now') - strftime('%s', p.deadline)) / 86400 as integer)"
            : "DATEDIFF(NOW(), p.deadline)";

        if ($professorId) {
            $prof = User::findOrFail($professorId);
            if (!in_array($prof->school_id, $schoolIds) && $user->role !== 'semed') {
                abort(403, 'Acesso negado.');
            }

            // Get professor stats
            $statsQuery = \Illuminate\Support\Facades\DB::table('documents as d')
                ->join('periods as p', 'd.period_id', '=', 'p.id')
                ->where('d.user_id', $professorId);

            $listQuery = \Illuminate\Support\Facades\DB::table('documents as d')
                ->join('periods as p', 'd.period_id', '=', 'p.id')
                ->where('d.user_id', $professorId)
                ->select('d.*', 'p.name as period_name', 'p.deadline');

            if ($period === 'monthly') {
                $month = date('m');
                $statsQuery->whereMonth('d.submitted_at', $month);
                $listQuery->whereMonth('d.submitted_at', $month);
            }

            $stats = $statsQuery->selectRaw("
                COUNT(d.id) as total_sent,
                SUM(CASE WHEN d.status = 'aprovado' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN d.status = 'rejeitado' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN d.status = 'ajustado' THEN 1 ELSE 0 END) as adjusted,
                SUM(CASE WHEN d.submitted_at <= p.deadline THEN 1 ELSE 0 END) as on_time,
                SUM(CASE WHEN d.submitted_at > p.deadline THEN 1 ELSE 0 END) as late_docs
            ")->first();

            $submissions = $listQuery->orderBy('d.submitted_at', 'desc')->get();

            $data = [
                'stats' => (array) $stats,
                'submissions' => $submissions
            ];
        } elseif ($type === 'pendencies') {
            // Find periods where a professor HAS NOT submitted anything yet and the deadline is past
            $data = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('schools as s', 'u.school_id', '=', 's.id')
                ->crossJoin('periods as p')
                ->leftJoin('documents as d', function($join) {
                    $join->on('u.id', '=', 'd.user_id')
                         ->on('p.id', '=', 'd.period_id');
                })
                ->where('u.role', '=', 'professor')
                ->where('p.school_id', '=', \Illuminate\Support\Facades\DB::raw('u.school_id'))
                ->where('p.deadline', '<', now())
                ->whereNull('d.id');

            if ($schoolId) {
                $data->where('s.id', $schoolId);
            }

            $data = $data->selectRaw("s.name as school_name, u.name as professor_name, p.name as period_name, p.deadline, $daysLateExpr as days_late")
                ->orderBy('days_late', 'desc')
                ->get();
        } elseif ($type === 'punctuality') {
            $data = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('documents as d', 'u.id', '=', 'd.user_id')
                ->where('u.role', '=', 'professor')
                ->whereIn('d.status', ['aprovado', 'ajustado', 'enviado']);

            if ($schoolId) {
                $data->where('u.school_id', $schoolId);
            }

            $data = $data->selectRaw("u.name as professor_name, u.profile_photo, AVG(d.score_final) as avg_score, COUNT(d.id) as total_docs")
                ->groupBy('u.id', 'u.name', 'u.profile_photo')
                ->orderBy('avg_score', 'desc')
                ->get();
        } else {
            // submissions
            $data = \Illuminate\Support\Facades\DB::table('users as u')
                ->join('schools as s', 'u.school_id', '=', 's.id')
                ->leftJoin('documents as d', 'u.id', '=', 'd.user_id')
                ->leftJoin('periods as p', 'd.period_id', '=', 'p.id')
                ->where('u.role', '=', 'professor');

            if ($schoolId) {
                $data->where('s.id', $schoolId);
            }

            $data = $data->selectRaw("
                s.name as school_name, u.name as professor_name, u.id as professor_id,
                COUNT(d.id) as total_sent,
                SUM(CASE WHEN d.status = 'aprovado' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN d.status = 'rejeitado' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN d.submitted_at > p.deadline THEN 1 ELSE 0 END) as late_docs
            ")
                ->groupBy('u.id', 's.name', 'u.name')
                ->orderBy('s.name')
                ->orderBy('u.name')
                ->get();
        }

        return view('school.reports', compact('type', 'data', 'schools', 'professors', 'schoolId', 'professorId', 'period', 'user'));
    }
}
