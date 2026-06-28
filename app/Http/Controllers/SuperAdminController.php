<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\School;
use App\Models\User;
use App\Models\AiQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuperAdminController extends Controller
{
    /**
     * Display the Super Admin Dashboard with SaaS KPIs.
     */
    public function index()
    {
        $stats = [
            'tenants_count' => Tenant::count(),
            'schools_count' => School::count(),
            'users_count' => User::count(),
            'ai_queries_count' => AiQuery::count(),
        ];

        // Fetch recent/active tenants for the dashboard overview
        $tenants = Tenant::latest()->take(5)->get();

        return view('dashboard.superadmin', compact('stats', 'tenants'));
    }

    /**
     * List all tenants in the administration panel.
     */
    public function tenants()
    {
        $tenants = Tenant::withCount(['schools', 'users'])->paginate(10);
        return view('superadmin.tenants.index', compact('tenants'));
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function tenantsCreate()
    {
        return view('superadmin.tenants.create');
    }

    /**
     * Store a newly created tenant in storage.
     */
    public function tenantsStore(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug'],
            'is_active' => ['boolean'],
            'ai_enabled' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        // Default booleans if they are not in request
        $validated['is_active'] = $request->has('is_active');
        $validated['ai_enabled'] = $request->has('ai_enabled');

        Tenant::create($validated);

        return redirect()->route('superadmin.tenants')
            ->with('success', 'Município parceiro (inquilino) cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function tenantsEdit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant in storage.
     */
    public function tenantsUpdate(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'is_active' => ['boolean'],
            'ai_enabled' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        // Handle checkboxes in Laravel form request
        $validated['is_active'] = $request->has('is_active');
        $validated['ai_enabled'] = $request->has('ai_enabled');

        $tenant->update($validated);

        return redirect()->route('superadmin.tenants')
            ->with('success', 'Configurações do município atualizadas com sucesso!');
    }

    /**
     * Toggle the status of the specified tenant.
     */
    public function tenantsToggleStatus(Tenant $tenant)
    {
        $tenant->update([
            'is_active' => !$tenant->is_active
        ]);

        $status = $tenant->is_active ? 'ativado' : 'desativado';
        return back()->with('success', "O município {$tenant->name} foi {$status} com sucesso!");
    }

    /**
     * Show the security settings page (change own password).
     */
    public function security()
    {
        return view('superadmin.security');
    }

    /**
     * Update the authenticated superadmin's own password.
     */
    public function updatePassword(Request $request)
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

        return redirect()->route('superadmin.security')->with('success', 'Senha alterada com sucesso!');
    }

    /**
     * Show the form for registering a new SEMED/Seduc (education department) for a tenant.
     */
    public function seducCreate()
    {
        $tenants = Tenant::orderBy('name')->get();
        return view('superadmin.seduc.create', compact('tenants'));
    }

    /**
     * Store a newly created SEMED/Seduc user, and set the tenant's school limit.
     */
    public function seducStore(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'max_schools_limit' => ['required', 'integer', 'min:1'],
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $tenant->update(['max_schools_limit' => $validated['max_schools_limit']]);

        $tempPassword = Str::password(10, symbols: false);

        User::create([
            'tenant_id' => $tenant->id,
            'school_id' => null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'password' => Hash::make($tempPassword),
            'role' => 'semed',
        ]);

        return redirect()->route('superadmin.seducs')
            ->with('success', "Seduc cadastrada com sucesso para {$tenant->name}! Senha inicial: {$tempPassword} (informe à secretaria e oriente a troca no primeiro acesso).");
    }

    /**
     * List all registered Seduc (SEMED) users across all tenants.
     */
    public function seducs()
    {
        $seducs = User::where('role', 'semed')
            ->with('tenant')
            ->orderBy('name')
            ->paginate(10);

        return view('superadmin.seducs.index', compact('seducs'));
    }

    /**
     * Show the form for editing a Seduc's contact details.
     */
    public function seducsEdit(User $user)
    {
        abort_unless($user->role === 'semed', 404);

        return view('superadmin.seducs.edit', compact('user'));
    }

    /**
     * Update a Seduc's contact details (name, email, whatsapp).
     */
    public function seducsUpdate(Request $request, User $user)
    {
        abort_unless($user->role === 'semed', 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return redirect()->route('superadmin.seducs')
            ->with('success', "Dados de contato de {$user->name} atualizados com sucesso!");
    }

    /**
     * Reset a Seduc's password to a new random temporary password.
     */
    public function seducsResetPassword(User $user)
    {
        abort_unless($user->role === 'semed', 404);

        $tempPassword = Str::password(10, symbols: false);

        $user->update([
            'password' => Hash::make($tempPassword),
        ]);

        return redirect()->route('superadmin.seducs')
            ->with('success', "Senha de {$user->name} redefinida para: {$tempPassword} (informe à secretaria e oriente a troca no primeiro acesso).");
    }
}
