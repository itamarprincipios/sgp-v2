<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Support\TempPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SemedController extends Controller
{
    /**
     * The supervisor role types a SEMED can register, keyed by role value.
     *
     * @var array<string, string>
     */
    public const SUPERVISOR_TYPES = [
        'supervisor_edfis' => 'Supervisor de Educação Física',
        'supervisor_monitor' => 'Supervisor de Monitoria (M.A.E)',
        'supervisor_infantil' => 'Supervisor de Educação Infantil',
        'supervisor_fundamental' => 'Supervisor do Ensino Fundamental',
    ];

    /*
    |--------------------------------------------------------------------------
    | Schools
    |--------------------------------------------------------------------------
    */

    /**
     * List schools registered for the SEMED's tenant.
     */
    public function schools(): View
    {
        $schools = School::withCount(['users', 'classes'])
            ->orderBy('name')
            ->paginate(10);

        return view('semed.schools.index', compact('schools'));
    }

    /**
     * Show the form for registering a new school.
     */
    public function schoolsCreate(): View
    {
        return view('semed.schools.create');
    }

    /**
     * Store a newly registered school.
     */
    public function schoolsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inep_code' => ['nullable', 'string', 'max:20', 'unique:schools,inep_code'],
            'address' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        School::create($validated);

        return redirect()->route('semed.schools')->with('success', 'Escola cadastrada com sucesso!');
    }

    /**
     * Show the form for editing a school.
     */
    public function schoolsEdit(School $school): View
    {
        return view('semed.schools.edit', compact('school'));
    }

    /**
     * Update a school's details.
     */
    public function schoolsUpdate(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'inep_code' => ['nullable', 'string', 'max:20', Rule::unique('schools', 'inep_code')->ignore($school->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $school->update($validated);

        return redirect()->route('semed.schools')->with('success', "Escola {$school->name} atualizada com sucesso!");
    }

    /**
     * Delete a school.
     */
    public function schoolsDelete(School $school): RedirectResponse
    {
        $school->delete();

        return redirect()->route('semed.schools')->with('success', 'Escola excluída com sucesso!');
    }

    /*
    |--------------------------------------------------------------------------
    | Directors
    |--------------------------------------------------------------------------
    */

    /**
     * List directors registered for the SEMED's tenant.
     */
    public function directors(): View
    {
        $directors = User::where('role', 'director')
            ->with('school')
            ->orderBy('name')
            ->paginate(10);

        $schools = School::orderBy('name')->get();

        return view('semed.directors.index', compact('directors', 'schools'));
    }

    /**
     * Show the form for registering a new director.
     */
    public function directorsCreate(): View
    {
        $schools = School::orderBy('name')->get();

        return view('semed.directors.create', compact('schools'));
    }

    /**
     * Store a newly registered director, linked to a single school.
     */
    public function directorsStore(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'school_id' => ['required', Rule::exists('schools', 'id')->where('tenant_id', $tenantId)],
        ]);

        $tempPassword = TempPassword::generate();

        $director = User::create([
            'tenant_id' => $tenantId,
            'school_id' => $validated['school_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'password' => Hash::make($tempPassword),
            'role' => 'director',
        ]);

        DB::table('user_schools')->insert([
            'user_id' => $director->id,
            'school_id' => $validated['school_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('semed.directors')
            ->with('success', "Diretor(a) cadastrado(a) com sucesso! Senha inicial: {$tempPassword} (informe ao diretor e oriente a troca no primeiro acesso).");
    }

    /**
     * Show the form for editing a director.
     */
    public function directorsEdit(User $user): View
    {
        abort_unless($user->role === 'director', 404);

        $schools = School::orderBy('name')->get();

        return view('semed.directors.edit', compact('user', 'schools'));
    }

    /**
     * Update a director's details, including their school assignment.
     */
    public function directorsUpdate(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'director', 404);

        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'school_id' => ['required', Rule::exists('schools', 'id')->where('tenant_id', $tenantId)],
        ]);

        $user->update($validated);

        DB::table('user_schools')->where('user_id', $user->id)->delete();
        DB::table('user_schools')->insert([
            'user_id' => $user->id,
            'school_id' => $validated['school_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('semed.directors')->with('success', "Dados de {$user->name} atualizados com sucesso!");
    }

    /**
     * Reset a director's password to a new random temporary password.
     */
    public function directorsResetPassword(User $user): RedirectResponse
    {
        abort_unless($user->role === 'director', 404);

        $tempPassword = TempPassword::generate();

        $user->update([
            'password' => Hash::make($tempPassword),
        ]);

        return redirect()->route('semed.directors')
            ->with('success', "Senha de {$user->name} redefinida para: {$tempPassword} (informe ao diretor e oriente a troca no primeiro acesso).");
    }

    /**
     * Delete a director.
     */
    public function directorsDelete(User $user): RedirectResponse
    {
        abort_unless($user->role === 'director', 404);

        $user->delete();

        return redirect()->route('semed.directors')->with('success', 'Diretor(a) excluído(a) com sucesso!');
    }

    /*
    |--------------------------------------------------------------------------
    | Supervisors
    |--------------------------------------------------------------------------
    */

    /**
     * List supervisors (all types) registered for the SEMED's tenant.
     */
    public function supervisors(): View
    {
        $supervisors = User::whereIn('role', array_keys(self::SUPERVISOR_TYPES))
            ->with('schools')
            ->orderBy('name')
            ->paginate(10);

        $schools = School::orderBy('name')->get();
        $supervisorTypes = self::SUPERVISOR_TYPES;

        return view('semed.supervisors.index', compact('supervisors', 'schools', 'supervisorTypes'));
    }

    /**
     * Show the form for registering a new supervisor.
     */
    public function supervisorsCreate(): View
    {
        $schools = School::orderBy('name')->get();
        $supervisorTypes = self::SUPERVISOR_TYPES;

        return view('semed.supervisors.create', compact('schools', 'supervisorTypes'));
    }

    /**
     * Store a newly registered supervisor, linked to one or more schools.
     */
    public function supervisorsStore(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_keys(self::SUPERVISOR_TYPES))],
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => [Rule::exists('schools', 'id')->where('tenant_id', $tenantId)],
        ]);

        $tempPassword = TempPassword::generate();

        $supervisor = User::create([
            'tenant_id' => $tenantId,
            'school_id' => null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'password' => Hash::make($tempPassword),
            'role' => $validated['role'],
        ]);

        $now = now();
        $pivotRows = array_map(fn ($schoolId) => [
            'user_id' => $supervisor->id,
            'school_id' => $schoolId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $validated['school_ids']);

        DB::table('user_schools')->insert($pivotRows);

        return redirect()->route('semed.supervisors')
            ->with('success', "Supervisor(a) cadastrado(a) com sucesso! Senha inicial: {$tempPassword} (informe ao supervisor e oriente a troca no primeiro acesso).");
    }

    /**
     * Show the form for editing a supervisor.
     */
    public function supervisorsEdit(User $user): View
    {
        abort_unless(array_key_exists($user->role, self::SUPERVISOR_TYPES), 404);

        $schools = School::orderBy('name')->get();
        $supervisorTypes = self::SUPERVISOR_TYPES;
        $assignedSchoolIds = $user->schools()->pluck('schools.id')->toArray();

        return view('semed.supervisors.edit', compact('user', 'schools', 'supervisorTypes', 'assignedSchoolIds'));
    }

    /**
     * Update a supervisor's details, including their school assignments.
     */
    public function supervisorsUpdate(Request $request, User $user): RedirectResponse
    {
        abort_unless(array_key_exists($user->role, self::SUPERVISOR_TYPES), 404);

        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_keys(self::SUPERVISOR_TYPES))],
            'school_ids' => ['required', 'array', 'min:1'],
            'school_ids.*' => [Rule::exists('schools', 'id')->where('tenant_id', $tenantId)],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'role' => $validated['role'],
        ]);

        DB::table('user_schools')->where('user_id', $user->id)->delete();

        $now = now();
        $pivotRows = array_map(fn ($schoolId) => [
            'user_id' => $user->id,
            'school_id' => $schoolId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $validated['school_ids']);

        DB::table('user_schools')->insert($pivotRows);

        return redirect()->route('semed.supervisors')->with('success', "Dados de {$user->name} atualizados com sucesso!");
    }

    /**
     * Reset a supervisor's password to a new random temporary password.
     */
    public function supervisorsResetPassword(User $user): RedirectResponse
    {
        abort_unless(array_key_exists($user->role, self::SUPERVISOR_TYPES), 404);

        $tempPassword = TempPassword::generate();

        $user->update([
            'password' => Hash::make($tempPassword),
        ]);

        return redirect()->route('semed.supervisors')
            ->with('success', "Senha de {$user->name} redefinida para: {$tempPassword} (informe ao supervisor e oriente a troca no primeiro acesso).");
    }

    /**
     * Delete a supervisor.
     */
    public function supervisorsDelete(User $user): RedirectResponse
    {
        abort_unless(array_key_exists($user->role, self::SUPERVISOR_TYPES), 404);

        $user->delete();

        return redirect()->route('semed.supervisors')->with('success', 'Supervisor(a) excluído(a) com sucesso!');
    }
}
