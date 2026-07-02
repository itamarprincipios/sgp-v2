<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Period;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\UserMedal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Redirect to the dynamic dashboard path based on role.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect($request->user()->dashboardPath());
    }

    /**
     * Admin Dashboard.
     */
    public function admin(Request $request): View
    {
        $schoolCount = School::count();
        $classCount = SchoolClass::count();
        $professorCount = User::where('role', 'professor')->count();
        $coordinatorCount = User::where('role', 'coordinator')->count();
        $directorCount = User::where('role', 'director')->count();
        $semedCount = User::where('role', 'semed')->count();

        $schools = School::withCount(['users', 'classes'])->with('director')->get();

        return view('dashboard.admin', compact(
            'schoolCount',
            'classCount',
            'professorCount',
            'coordinatorCount',
            'directorCount',
            'semedCount',
            'schools'
        ));
    }

    /**
     * SEMED Dashboard.
     */
    public function semed(Request $request): View
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;

        // Fallback safety if tenant_id is null
        if (!$tenantId) {
            $tenantId = 1;
        }

        $filter = $request->input('filter', 'annual');
        $year = date('Y');

        $schoolCount = School::where('tenant_id', $tenantId)->count();
        $classCount = SchoolClass::whereHas('school', function($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->count();
        $professorCount = User::where('tenant_id', $tenantId)->where('role', 'professor')->count();
        $coordinatorCount = User::where('tenant_id', $tenantId)->where('role', 'coordinator')->count();
        $directorCount = User::where('tenant_id', $tenantId)->where('role', 'director')->count();

        $schools = School::where('tenant_id', $tenantId)->withCount(['users', 'classes'])->with('director')->get();

        $totalPlannings = Period::where('tenant_id', $tenantId)->count();
        $totalDocs = Document::where('tenant_id', $tenantId)->count();

        // Detect SQLite database driver
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $caseExpr = $isSqlite 
            ? 'SUM(CASE WHEN datetime(d.submitted_at) <= datetime(p.deadline) THEN 1 ELSE 0 END)'
            : 'SUM(CASE WHEN d.submitted_at <= p.deadline THEN 1 ELSE 0 END)';

        // 1. School Ranking
        $schoolQuery = \Illuminate\Support\Facades\DB::table('schools as s')
            ->join('users as u', 's.id', '=', 'u.school_id')
            ->join('documents as d', 'u.id', '=', 'd.user_id')
            ->join('periods as p', 'd.period_id', '=', 'p.id')
            ->where('s.tenant_id', '=', $tenantId)
            ->where('d.status', '=', 'aprovado')
            ->whereYear('d.submitted_at', $year);

        if ($filter === '1' || $filter === '2' || $filter === '3' || $filter === '4') {
            $bimestre = intval($filter);
            $months = [($bimestre * 2) - 1, $bimestre * 2];
            if ($isSqlite) {
                $schoolQuery->where(function($q) use ($months) {
                    foreach ($months as $m) {
                        $q->orWhereRaw("CAST(strftime('%m', d.submitted_at) as integer) = ?", [$m]);
                    }
                });
            } else {
                $schoolQuery->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(d.submitted_at)'), $months);
            }
        } elseif (strlen($filter) === 2) {
            $schoolQuery->whereMonth('d.submitted_at', intval($filter));
        }

        $rankSchools = $schoolQuery
            ->selectRaw("s.name as school_name, COUNT(d.id) as total_docs, (CAST($caseExpr as float) / COUNT(d.id)) * 100 as punctuality_percentage")
            ->groupBy('s.id', 's.name')
            ->orderBy('punctuality_percentage', 'desc')
            ->limit(3)
            ->get();

        // 2. Professor Query Base
        $profQuery = \Illuminate\Support\Facades\DB::table('users as u')
            ->join('schools as s', 'u.school_id', '=', 's.id')
            ->join('documents as d', 'u.id', '=', 'd.user_id')
            ->where('u.tenant_id', '=', $tenantId)
            ->where('u.role', '=', 'professor')
            ->where('d.status', '=', 'aprovado')
            ->whereYear('d.submitted_at', $year);

        if ($filter === '1' || $filter === '2' || $filter === '3' || $filter === '4') {
            $bimestre = intval($filter);
            $months = [($bimestre * 2) - 1, $bimestre * 2];
            if ($isSqlite) {
                $profQuery->where(function($q) use ($months) {
                    foreach ($months as $m) {
                        $q->orWhereRaw("CAST(strftime('%m', d.submitted_at) as integer) = ?", [$m]);
                    }
                });
            } else {
                $profQuery->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(d.submitted_at)'), $months);
            }
        } elseif (strlen($filter) === 2) {
            $profQuery->whereMonth('d.submitted_at', intval($filter));
        }

        // Regular Professors Ranking
        $rankProfessors = (clone $profQuery)
            ->where('u.is_monitor', '=', 0)
            ->where('u.is_physical_education', '=', 0)
            ->selectRaw('u.name as professor_name, u.whatsapp, s.name as school_name, SUM(d.score_final) as total_points')
            ->groupBy('u.id', 'u.name', 'u.whatsapp', 's.name')
            ->orderBy('total_points', 'desc')
            ->limit(3)
            ->get();

        // Monitors Ranking
        $rankMonitors = (clone $profQuery)
            ->where('u.is_monitor', '=', 1)
            ->selectRaw('u.name as professor_name, u.whatsapp, s.name as school_name, SUM(d.score_final) as total_points')
            ->groupBy('u.id', 'u.name', 'u.whatsapp', 's.name')
            ->orderBy('total_points', 'desc')
            ->limit(3)
            ->get();

        // 3. Coordinator Ranking
        $coordQuery = \Illuminate\Support\Facades\DB::table('users as uc')
            ->join('schools as s', 'uc.school_id', '=', 's.id')
            ->join('users as up', 's.id', '=', 'up.school_id')
            ->join('documents as d', 'up.id', '=', 'd.user_id')
            ->join('periods as p', 'd.period_id', '=', 'p.id')
            ->where('uc.tenant_id', '=', $tenantId)
            ->where('uc.role', '=', 'coordinator')
            ->where('up.role', '=', 'professor')
            ->where('d.status', '=', 'aprovado')
            ->whereYear('d.submitted_at', $year);

        if ($filter === '1' || $filter === '2' || $filter === '3' || $filter === '4') {
            $bimestre = intval($filter);
            $months = [($bimestre * 2) - 1, $bimestre * 2];
            if ($isSqlite) {
                $coordQuery->where(function($q) use ($months) {
                    foreach ($months as $m) {
                        $q->orWhereRaw("CAST(strftime('%m', d.submitted_at) as integer) = ?", [$m]);
                    }
                });
            } else {
                $coordQuery->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(d.submitted_at)'), $months);
            }
        } elseif (strlen($filter) === 2) {
            $coordQuery->whereMonth('d.submitted_at', intval($filter));
        }

        $rankCoordinators = $coordQuery
            ->selectRaw("uc.name as coordinator_name, uc.whatsapp, s.name as school_name, COUNT(d.id) as total_docs, (CAST($caseExpr as float) / COUNT(d.id)) * 100 as punctuality_percentage")
            ->groupBy('uc.id', 'uc.name', 'uc.whatsapp', 's.name')
            ->orderBy('punctuality_percentage', 'desc')
            ->limit(3)
            ->get();

        return view('dashboard.semed', compact(
            'schoolCount',
            'classCount',
            'professorCount',
            'coordinatorCount',
            'directorCount',
            'schools',
            'user',
            'filter',
            'totalPlannings',
            'totalDocs',
            'rankSchools',
            'rankProfessors',
            'rankMonitors',
            'rankCoordinators'
        ));
    }

    /**
     * School (Director/Coordinator) Dashboard.
     */
    public function school(Request $request): View
    {
        $user = $request->user();
        
        // Get all assigned schools (pivot + main)
        $schoolIds = $user->getAssignedSchoolIds();
        
        if (empty($schoolIds)) {
            $schoolIds = [0];
        }
        
        $schools = School::whereIn('id', $schoolIds)->get();
        $school = $schools->first();
        
        // Count stats
        // 1. Professors
        $professorsCount = User::whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->count();
            
        // 2. Classes
        $classesCount = SchoolClass::whereIn('school_id', $schoolIds)->count();
        
        // 3. Periods (Plannings)
        $periodsCount = Period::where('is_active', true)
            ->where(function($query) use ($schoolIds) {
                $query->whereNull('school_id')
                      ->orWhereIn('school_id', $schoolIds);
            })
            ->count();
            
        // 4. Pending submissions
        $pendingCount = 0;
        $allPeriods = Period::where('is_active', true)
            ->where(function($query) use ($schoolIds) {
                $query->whereNull('school_id')
                      ->orWhereIn('school_id', $schoolIds);
            })
            ->get();
            
        $professors = User::whereIn('school_id', $schoolIds)
            ->where('role', 'professor')
            ->get();
            
        foreach ($allPeriods as $period) {
            $isPE = $period->is_physical_education;
            $isMonitor = $period->is_monitor;
            $isFirstGrade = $period->is_first_grade;
            
            $targetProfs = $professors->filter(function($p) use ($isPE, $isMonitor, $isFirstGrade) {
                if ($isMonitor && !$p->is_monitor) return false;
                if ($isPE && !$p->is_physical_education) return false;
                if ($isFirstGrade && !$p->is_first_grade) return false;
                return true;
            });
            
            foreach ($targetProfs as $prof) {
                $hasDoc = Document::where('user_id', $prof->id)
                    ->where('period_id', $period->id)
                    ->exists();
                if (!$hasDoc) {
                    $pendingCount++;
                }
            }
        }
        
        // Get recent documents
        $recentDocuments = Document::whereIn('user_id', $professors->pluck('id'))
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
            
        // New uploads count
        $newUploadsCount = 0;
        $lastViewed = session('last_viewed_uploads');
        $unreadUploads = Document::whereIn('user_id', $professors->pluck('id'))
            ->whereIn('status', ['enviado', 'atrasado'])
            ->get();
            
        foreach ($unreadUploads as $doc) {
            if (!$lastViewed || $doc->submitted_at->gt($lastViewed)) {
                $newUploadsCount++;
            }
        }

        // Gamification metrics (Director Only)
        $globalPunctuality = [];
        $topProfessors = [];
        $coordStats = [];
        $mySchoolRank = 1;
        $mySchoolData = ['school_name' => $school->name ?? 'N/A', 'avg_score' => 100.0, 'total_docs' => 0];

        if (in_array($user->role, ['director', 'vice_director'])) {
            $schoolsStats = School::where('tenant_id', $user->tenant_id)->get()->map(function($s) {
                $docs = Document::whereHas('user', function($q) use ($s) {
                    $q->where('school_id', $s->id);
                })->get();
                $avg = $docs->count() > 0 ? ($docs->sum('score_final') / ($docs->count() * 10)) * 100 : 100.0;
                return [
                    'school_name' => $s->name,
                    'avg_score' => $avg,
                    'total_docs' => $docs->count()
                ];
            })->sortByDesc('avg_score')->values()->all();

            $globalPunctuality = $schoolsStats;

            foreach ($globalPunctuality as $idx => $row) {
                if ($row['school_name'] === ($school->name ?? '')) {
                    $mySchoolRank = $idx + 1;
                    $mySchoolData = $row;
                    break;
                }
            }

            // Top professors
            $profStats = [];
            foreach ($professors as $p) {
                $docs = Document::where('user_id', $p->id)->whereIn('status', ['enviado', 'atrasado', 'aprovado', 'ajustado'])->get();
                $ontime = $docs->where('status', 'aprovado')->count();
                $profStats[] = [
                    'name' => $p->name,
                    'school_name' => $school->name ?? '',
                    'points' => $ontime * 5,
                    'whatsapp' => $p->whatsapp,
                    'id' => $p->id
                ];
            }
            usort($profStats, function($a, $b) { return $b['points'] <=> $a['points']; });
            $topProfessors = array_slice($profStats, 0, 3);

            // Coordinators ranking
            $coordinatorsList = User::whereIn('school_id', $schoolIds)->where('role', 'coordinator')->get();
            foreach ($coordinatorsList as $c) {
                $coordStats[] = [
                    'name' => $c->name,
                    'school_name' => $school->name ?? '',
                    'punctuality' => number_format($mySchoolData['avg_score'], 1),
                    'whatsapp' => $c->whatsapp,
                    'id' => $c->id
                ];
            }
        }

        $coordinators = User::whereIn('school_id', $schoolIds)->where('role', 'coordinator')->get();
        $allUploads = Document::whereIn('user_id', $professors->pluck('id'))
            ->with(['user', 'period'])
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard.school', compact(
            'user',
            'school',
            'schools',
            'professorsCount',
            'classesCount',
            'periodsCount',
            'pendingCount',
            'recentDocuments',
            'newUploadsCount',
            'globalPunctuality',
            'topProfessors',
            'coordStats',
            'mySchoolRank',
            'mySchoolData',
            'coordinators',
            'allUploads'
        ));
    }

    /**
     * Professor Dashboard.
     */
    public function professor(Request $request): View
    {
        $user = $request->user();
        
        // Fetch professor documents
        $documents = Document::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();
            
        // Calculate points
        $totalPoints = $documents->whereIn('status', ['enviado', 'atrasado', 'aprovado', 'ajustado'])
            ->sum('score_final');
            
        // Active Profile context (Titular / Monitor)
        $activeProfile = session('active_profile', 'titular');
        
        if ($activeProfile === 'monitor' && !$user->is_monitor) {
            $activeProfile = 'titular';
            session(['active_profile' => 'titular']);
        }
        
        $isMonitorFlag = ($activeProfile === 'monitor') ? 1 : 0;
        $isPEFlag = ($activeProfile === 'titular' && $user->is_physical_education) ? 1 : 0;
        $isFirstGradeFlag = ($activeProfile === 'titular' && $user->is_first_grade) ? 1 : 0;

        // Fetch planning periods
        $periods = Period::where('is_active', true)
            ->where(function($query) use ($user) {
                $query->whereNull('school_id')
                      ->orWhere('school_id', $user->school_id);
            })
            ->where('is_physical_education', $isPEFlag)
            ->get();

        // Get school data
        $schoolData = $user->school;
        
        // Determine class name
        $className = 'Não vinculada';
        if ($activeProfile === 'monitor' && $user->monitor_class_id) {
            $className = $user->monitorClass->name . ' (Monitoria M.A.E)';
        } elseif ($user->is_physical_education) {
            $className = 'Educação Física';
        } elseif ($user->class_id) {
            $className = $user->schoolClass->name;
        }

        // Fetch user medals
        $medals = UserMedal::where('user_id', $user->id)->get();

        // Get coordinator contact
        $coordinatorPhone = null;
        if ($schoolData) {
            $coordinator = User::where('school_id', $schoolData->id)
                ->where('role', 'coordinator')
                ->whereNotNull('whatsapp')
                ->first();
            if ($coordinator) {
                $coordinatorPhone = $coordinator->whatsapp;
            }
        }

        return view('dashboard.professor', compact(
            'user',
            'documents',
            'periods',
            'medals',
            'totalPoints',
            'schoolData',
            'className',
            'coordinatorPhone',
            'activeProfile'
        ));
    }

    /**
     * Supervisor Physical Education Dashboard.
     */
    public function supervisorEdfis(Request $request): View
    {
        return view('dashboard.supervisor_edfis');
    }

    /**
     * Supervisor Monitor Dashboard.
     */
    public function supervisorMonitor(Request $request): View
    {
        return view('dashboard.supervisor_monitor');
    }

    /**
     * Supervisor Early Childhood Education Dashboard.
     */
    public function supervisorInfantil(Request $request): View
    {
        return view('dashboard.supervisor_infantil');
    }

    /**
     * Supervisor Elementary Education Dashboard.
     */
    public function supervisorFundamental(Request $request): View
    {
        return view('dashboard.supervisor_fundamental');
    }

    /**
     * SEMED Security settings page (change own password).
     */
    public function semedSecurity(): View
    {
        return view('semed.security');
    }

    /**
     * Update the authenticated SEMED user's own password.
     */
    public function semedUpdatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'A senha atual informada está incorreta.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('semed.security')->with('success', 'Senha alterada com sucesso!');
    }
}
