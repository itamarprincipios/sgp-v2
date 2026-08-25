<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantAiPrompt;
use App\Models\TenantReferenceFile;
use App\Services\DocumentExtractor;
use Illuminate\Support\Facades\File;
use App\Services\PromptSettings;
use App\Models\School;
use App\Models\User;
use App\Models\AiQuery;
use App\Services\TenantProvisioner;
use App\Support\TempPassword;
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
            'expires_at' => ['nullable', 'date'],
            'plan' => ['nullable', 'in:coordenador,escola,semed'],
            'billing_type' => ['nullable', 'in:mensal,vitalicio'],
            'ai_monthly_limit' => ['nullable', 'integer', 'min:1'],
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
     * Tela de personalização dos prompts da IANNE deste município.
     */
    public function iannePrompts(Tenant $tenant)
    {
        $blocos = PromptSettings::for($tenant->id);
        $padroes = PromptSettings::defaults();
        $arquivos = TenantReferenceFile::where('tenant_id', $tenant->id)->orderByDesc('id')->get();

        return view('superadmin.tenants.ianne', compact('tenant', 'blocos', 'padroes', 'arquivos'));
    }

    /**
     * Salva os blocos editados. Campo apagado volta ao padrão (PromptSettings
     * trata vazio como "usa o padrão"), então não há como deixar um buraco no
     * meio do prompt.
     */
    public function iannePromptsUpdate(Request $request, Tenant $tenant)
    {
        $regras = [];
        foreach (array_keys(PromptSettings::BLOCKS) as $chave) {
            $regras["blocks.{$chave}"] = ['nullable', 'string', 'max:8000'];
        }
        $request->validate($regras);

        $blocos = [];
        foreach (array_keys(PromptSettings::BLOCKS) as $chave) {
            $blocos[$chave] = trim((string) $request->input("blocks.{$chave}", ''));
        }

        TenantAiPrompt::updateOrCreate(
            ['tenant_id' => $tenant->id],
            ['blocks' => $blocos],
        );

        return redirect()->route('superadmin.tenants.ianne', $tenant)
            ->with('success', "Prompts da IANNE atualizados para {$tenant->name}. Valem a partir da próxima análise.");
    }

    /**
     * Recebe um arquivo de referência da rede (modelo de requisitos, portaria,
     * rubrica) e já extrai o texto — é o texto, não o arquivo, que vai ao
     * prompt do parecer.
     */
    public function ianneFileStore(Request $request, Tenant $tenant, DocumentExtractor $extractor)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:docx,pdf,txt', 'max:10240'],
            'title' => ['nullable', 'string', 'max:255'],
        ], [
            'file.mimes' => 'Formato não suportado. Envie Word (.docx), PDF ou texto (.txt).',
            'file.max' => 'Arquivo muito grande (máximo 10MB).',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = $tenant->id . '_' . time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);

        $destino = public_path('uploads/referencias');
        if (!File::isDirectory($destino)) {
            File::makeDirectory($destino, 0755, true);
        }
        $file->move($destino, $fileName);
        $caminho = $destino . DIRECTORY_SEPARATOR . $fileName;

        // .txt é lido direto; .docx sai do ZipArchive local; .pdf passa pela IA.
        $texto = '';
        $erro = null;
        try {
            $texto = $extension === 'txt'
                ? (string) File::get($caminho)
                : $extractor->extractText($caminho);
        } catch (\Throwable $e) {
            $erro = $e->getMessage();
        }

        $texto = trim($texto);
        $ok = $texto !== '' && !str_starts_with($texto, '[Arquivo');

        $arquivo = TenantReferenceFile::create([
            'tenant_id' => $tenant->id,
            'title' => trim((string) $request->input('title')) ?: pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $fileName,
            'extension' => $extension,
            'content_text' => $ok ? $texto : null,
            'chars' => $ok ? mb_strlen($texto) : 0,
            'extraction_ok' => $ok,
            'is_active' => $ok,
            'uploaded_by' => auth()->id(),
        ]);

        if (!$ok) {
            $motivo = $erro
                ? "A leitura falhou: {$erro}"
                : 'Não foi possível extrair texto — se for um PDF digitalizado (imagem) ou protegido, a leitura automática não funciona.';

            return redirect()->route('superadmin.tenants.ianne', $tenant)
                ->with('error', "\"{$arquivo->title}\" foi guardado, mas está INATIVO e não entra nas análises. {$motivo} Converta para .docx e envie de novo.");
        }

        $aviso = $arquivo->provavelmenteTruncado()
            ? ' ATENÇÃO: PDF longo — a extração pode ter sido cortada. Confira o texto ou envie em .docx.'
            : '';

        return redirect()->route('superadmin.tenants.ianne', $tenant)
            ->with('success', "\"{$arquivo->title}\" enviado: " . number_format($arquivo->chars, 0, ',', '.') . " caracteres extraídos. Passa a valer na próxima análise.{$aviso}");
    }

    /**
     * Liga/desliga um arquivo sem excluí-lo.
     */
    public function ianneFileToggle(Tenant $tenant, TenantReferenceFile $file)
    {
        abort_unless($file->tenant_id === $tenant->id, 404);
        abort_if(!$file->extraction_ok && !$file->is_active, 422, 'Arquivo sem texto extraído não pode ser ativado.');

        $file->update(['is_active' => !$file->is_active]);

        return redirect()->route('superadmin.tenants.ianne', $tenant)
            ->with('success', "\"{$file->title}\" " . ($file->is_active ? 'passou a valer' : 'saiu') . ' nas análises deste município.');
    }

    /**
     * Remove o arquivo do fluxo (registro + arquivo físico).
     */
    public function ianneFileDestroy(Tenant $tenant, TenantReferenceFile $file)
    {
        abort_unless($file->tenant_id === $tenant->id, 404);

        $caminho = public_path('uploads/referencias/' . $file->file_path);
        if (File::exists($caminho)) {
            File::delete($caminho);
        }

        $titulo = $file->title;
        $file->delete();

        return redirect()->route('superadmin.tenants.ianne', $tenant)
            ->with('success', "\"{$titulo}\" removido.");
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
            'plan' => ['sometimes', 'required', 'in:coordenador,escola,semed'],
            'billing_type' => ['nullable', 'in:mensal,vitalicio'],
            'ai_monthly_limit' => ['nullable', 'integer', 'min:1'],
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

    /** Assistente "Nova venda" (spec §4.2) */
    public function saleCreate()
    {
        return view('superadmin.sale_create');
    }

    public function saleStore(Request $request, TenantProvisioner $provisioner)
    {
        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:coordenador,escola,semed'],
            'billing_type' => ['required', 'in:mensal,vitalicio'],
            'expires_at' => ['required_if:billing_type,mensal', 'nullable', 'date'],
            'create_default_classes' => ['nullable', 'boolean'],
            'school_name' => ['required_unless:plan,semed', 'nullable', 'string', 'max:255'],
            'coordinator_name' => ['required_if:plan,coordenador', 'required_with:coordinator_email', 'nullable', 'string', 'max:255'],
            'coordinator_email' => ['required_if:plan,coordenador', 'nullable', 'email', 'unique:users,email'],
            'director_name' => ['required_if:plan,escola', 'nullable', 'string', 'max:255'],
            'director_email' => ['required_if:plan,escola', 'nullable', 'email', 'unique:users,email'],
            'vice_name' => ['nullable', 'required_with:vice_email', 'string', 'max:255'],
            'vice_email' => ['nullable', 'email', 'unique:users,email'],
            'semed_name' => ['required_if:plan,semed', 'nullable', 'string', 'max:255'],
            'semed_email' => ['required_if:plan,semed', 'nullable', 'email', 'unique:users,email'],
        ]);

        $validated['create_default_classes'] = $request->boolean('create_default_classes');

        $result = $provisioner->provision($validated);

        return view('superadmin.sale_result', [
            'tenant' => $result['tenant'],
            'credentials' => $result['credentials'],
        ]);
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

        $tempPassword = TempPassword::generate();

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

        $tempPassword = TempPassword::generate();

        $user->update([
            'password' => Hash::make($tempPassword),
        ]);

        return redirect()->route('superadmin.seducs')
            ->with('success', "Senha de {$user->name} redefinida para: {$tempPassword} (informe à secretaria e oriente a troca no primeiro acesso).");
    }
}
