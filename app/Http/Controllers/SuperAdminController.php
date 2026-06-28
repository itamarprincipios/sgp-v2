<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\School;
use App\Models\User;
use App\Models\AiQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
            'max_schools_limit' => ['required', 'integer', 'min:1'],
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
            'max_schools_limit' => ['required', 'integer', 'min:1'],
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
}
