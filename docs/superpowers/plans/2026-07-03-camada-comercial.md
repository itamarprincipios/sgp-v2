# Camada Comercial (3 modelos de venda) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir vender o SGP em 3 planos (coordenador / escola / semed) com assinatura mensal ou vitalícia, modo somente leitura ao vencer, provisionamento em 1 tela e cota de IA preparada.

**Architecture:** Plano e cobrança moram na tabela `tenants` (Abordagem A do spec). Um middleware `subscription` no grupo autenticado bloqueia escrita quando vencido; um serviço `TenantProvisioner` cria tenant+escola+usuários por plano; o `AIService` (único ponto de chamada Gemini) conta uso em `ai_usage` e checa cota.

**Tech Stack:** Laravel 10 / PHP 8.3 (prod), Blade + Tailwind + Alpine.js, MySQL 8.

**Spec:** `docs/superpowers/specs/2026-07-03-camada-comercial-design.md`

## Global Constraints

- **Sem ambiente local de execução.** Não há DB local nem servidor local confiável. Verificação por tarefa: `php -l` em cada arquivo PHP tocado + smoke test manual em produção após o push. NÃO rodar `php artisan migrate` localmente.
- **Todo push para `main` vai para produção (autodeploy Hostinger).** Cada tarefa termina com commit + push. Nunca deixar código quebrado entre tarefas.
- **`npm run build` antes de qualquer push que toque blade/CSS/JS** (a pasta `public/build/` está no Git).
- **O banco de produção JÁ TEM** as colunas novas de `tenants` e a tabela `ai_usage` (SQL executado em 03/07/2026 — spec §2.3). As migrations são só para histórico e DEVEM ser guardadas com `Schema::hasColumn`/`hasTable`.
- Mensagens de UI em português brasileiro. WhatsApp de renovação: `https://wa.me/5595991248941`.
- Convenções do projeto: cards `bg-slate-900/60 border border-slate-800 rounded-xl`, botões primários `bg-indigo-600 hover:bg-indigo-500`, formulários com `@csrf`, views superadmin em `resources/views/superadmin/`.

---

### Task 1: Migrations guardadas + model Tenant

**Files:**
- Create: `database/migrations/2026_07_03_000001_add_commercial_fields_to_tenants_table.php`
- Create: `database/migrations/2026_07_03_000002_create_ai_usage_table.php`
- Modify: `app/Models/Tenant.php`

**Interfaces:**
- Produces: `Tenant::isBlocked(): bool` (usado pelo middleware da Task 2), campos fillable `plan`, `billing_type`, `ai_monthly_limit` (usados pelas Tasks 3–5).

- [ ] **Step 1: Criar migration dos campos comerciais (guardada)**

```php
<?php
// database/migrations/2026_07_03_000001_add_commercial_fields_to_tenants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SQL equivalente já executado em produção em 03/07/2026 (spec §2.3).
     * Guardas hasColumn evitam erro caso rode num banco já alterado.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'plan')) {
                $table->enum('plan', ['coordenador', 'escola', 'semed'])
                    ->default('semed')->after('slug');
            }
            if (!Schema::hasColumn('tenants', 'billing_type')) {
                $table->enum('billing_type', ['mensal', 'vitalicio'])
                    ->nullable()->after('plan');
            }
            if (!Schema::hasColumn('tenants', 'ai_monthly_limit')) {
                $table->unsignedInteger('ai_monthly_limit')
                    ->nullable()->after('ai_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plan', 'billing_type', 'ai_monthly_limit']);
        });
    }
};
```

- [ ] **Step 2: Criar migration da ai_usage (guardada)**

```php
<?php
// database/migrations/2026_07_03_000002_create_ai_usage_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tabela já criada em produção em 03/07/2026 (spec §2.3). */
    public function up(): void
    {
        if (Schema::hasTable('ai_usage')) {
            return;
        }
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->char('period', 7); // "2026-07"
            $table->unsignedInteger('count')->default(0);
            $table->unique(['tenant_id', 'period'], 'ai_usage_tenant_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
    }
};
```

- [ ] **Step 3: Atualizar model Tenant**

Em `app/Models/Tenant.php`, adicionar aos `$fillable` (depois de `'slug'`): `'plan'`, `'billing_type'`, e (depois de `'ai_enabled'`) `'ai_monthly_limit'`. Adicionar o método abaixo após `casts`:

```php
    /**
     * Assinatura bloqueada (modo somente leitura): desativado manualmente
     * ou mensalidade vencida. Vitalício (expires_at null) nunca bloqueia.
     */
    public function isBlocked(): bool
    {
        if (!$this->is_active) {
            return true;
        }
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
```

- [ ] **Step 4: Verificar sintaxe**

Run: `php -l database/migrations/2026_07_03_000001_add_commercial_fields_to_tenants_table.php; php -l database/migrations/2026_07_03_000002_create_ai_usage_table.php; php -l app/Models/Tenant.php`
Expected: `No syntax errors detected` ×3

- [ ] **Step 5: Commit + push**

```bash
git add database/migrations/2026_07_03_000001_add_commercial_fields_to_tenants_table.php database/migrations/2026_07_03_000002_create_ai_usage_table.php app/Models/Tenant.php
git commit -m "feat: campos comerciais no Tenant + tabela ai_usage (migrations guardadas, SQL ja aplicado em prod)"
git push origin main
```

---

### Task 2: Middleware CheckSubscription + banner + ai_enabled no RAG

**Files:**
- Create: `app/Http/Middleware/CheckSubscription.php`
- Modify: `app/Http/Kernel.php:66` (alias novo)
- Modify: `routes/web.php:25` (adicionar ao grupo)
- Modify: `resources/views/layouts/app.blade.php` (banner antes do `<header>`)
- Modify: `app/Http/Controllers/RAGController.php` (respeitar `ai_enabled`)

**Interfaces:**
- Consumes: `Tenant::isBlocked()` (Task 1).
- Produces: view share `subscriptionReadOnly` (bool) para os layouts.

- [ ] **Step 1: Criar o middleware**

```php
<?php
// app/Http/Middleware/CheckSubscription.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforcement da assinatura (spec §3): tenant vencido/desativado entra em
 * modo somente leitura — GET passa (com banner), escrita é barrada.
 * Superadmin (sem tenant) nunca é afetado. Logout fica em routes/auth.php,
 * fora deste grupo, então segue acessível.
 */
class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = $user?->tenant;

        if (!$user || $user->role === 'superadmin' || !$tenant) {
            return $next($request);
        }

        if (!$tenant->isBlocked()) {
            return $next($request);
        }

        View::share('subscriptionReadOnly', true);

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        $message = 'Assinatura vencida — modo somente leitura. Fale conosco para renovar.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 403);
        }

        return back()->with('error', $message);
    }
}
```

- [ ] **Step 2: Registrar alias e aplicar ao grupo**

Em `app/Http/Kernel.php`, adicionar após a linha `'role' => ...`:

```php
        'subscription' => \App\Http\Middleware\CheckSubscription::class,
```

Em `routes/web.php`, trocar a linha 25:

```php
Route::middleware(['auth', 'verified'])->group(function () {
```

por:

```php
Route::middleware(['auth', 'verified', 'subscription'])->group(function () {
```

- [ ] **Step 3: Banner no layout**

Em `resources/views/layouts/app.blade.php`, imediatamente ANTES da linha `<header class="sticky top-0 z-10 ...` (linha ~121), inserir:

```blade
                @if(!empty($subscriptionReadOnly))
                    <div class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2.5 flex flex-wrap items-center justify-center gap-2 text-center">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>Assinatura vencida — modo somente leitura.</span>
                        <a href="https://wa.me/5595991248941" target="_blank" class="underline font-bold hover:text-slate-700">Fale conosco para renovar</a>
                    </div>
                @endif
```

- [ ] **Step 4: Respeitar ai_enabled no RAGController**

Em `app/Http/Controllers/RAGController.php`, no método `query()`, logo APÓS o bloco que valida `$allowedRoles` (por volta da linha 44), inserir:

```php
            // Interruptor de IA por tenant (spec §3): desliga só a IA sem bloquear o resto
            if ($user->tenant && !$user->tenant->ai_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'A assistente de IA está desativada para a sua conta. Fale com o suporte.',
                ], 403);
            }
```

- [ ] **Step 5: Verificar sintaxe + build**

Run: `php -l app/Http/Middleware/CheckSubscription.php; php -l app/Http/Kernel.php; php -l routes/web.php; php -l app/Http/Controllers/RAGController.php`
Expected: `No syntax errors detected` ×4
Run: `npm run build`
Expected: build Vite sem erros.

- [ ] **Step 6: Commit + push**

```bash
git add app/Http/Middleware/CheckSubscription.php app/Http/Kernel.php routes/web.php resources/views/layouts/app.blade.php app/Http/Controllers/RAGController.php public/build
git commit -m "feat: middleware de assinatura com modo somente leitura + banner + ai_enabled no RAG"
git push origin main
```

- [ ] **Step 7: Smoke test em produção**

No phpMyAdmin: `UPDATE tenants SET is_active = 0 WHERE id = <tenant de teste>;`
Logar com usuário desse tenant e conferir: banner âmbar aparece; páginas abrem; qualquer POST (ex: criar turma) volta com erro; IA recusa.
Reverter: `UPDATE tenants SET is_active = 1 WHERE id = <tenant de teste>;`

---

### Task 3: Contador ai_usage + cota no AIService

**Files:**
- Create: `app/Exceptions/AiQuotaExceededException.php`
- Create: `app/Services/AiQuota.php`
- Modify: `app/Services/AIService.php` (dentro de `generateContent()`)
- Modify: `app/Http/Controllers/RAGController.php` (catch da exceção)
- Modify: `app/Services/MetadataInference.php` (catch da exceção → retorna null, upload segue manual)

**Interfaces:**
- Consumes: campo `ai_monthly_limit` (Task 1).
- Produces: `AiQuota::assertAvailable(?\App\Models\Tenant $tenant): void` (lança `AiQuotaExceededException`), `AiQuota::record(?int $tenantId): void`.

- [ ] **Step 1: Criar a exceção**

```php
<?php
// app/Exceptions/AiQuotaExceededException.php

namespace App\Exceptions;

use Exception;

class AiQuotaExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct('Cota mensal de IA atingida. Renove seu plano ou aguarde o próximo mês.');
    }
}
```

- [ ] **Step 2: Criar o serviço de cota**

```php
<?php
// app/Services/AiQuota.php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Cota mensal de IA por tenant (spec §5). ai_monthly_limit null = ilimitado
 * (período de testes). O contador registra SEMPRE, mesmo sem limite, para
 * gerar dado real de consumo antes da precificação.
 */
class AiQuota
{
    public static function assertAvailable(?Tenant $tenant): void
    {
        if (!$tenant || $tenant->ai_monthly_limit === null) {
            return;
        }

        $used = DB::table('ai_usage')
            ->where('tenant_id', $tenant->id)
            ->where('period', now()->format('Y-m'))
            ->value('count') ?? 0;

        if ($used >= $tenant->ai_monthly_limit) {
            throw new AiQuotaExceededException();
        }
    }

    public static function record(?int $tenantId): void
    {
        if (!$tenantId) {
            return; // chamadas do superadmin não contam contra ninguém
        }

        DB::statement(
            'INSERT INTO ai_usage (tenant_id, period, count) VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE count = count + 1',
            [$tenantId, now()->format('Y-m')]
        );
    }
}
```

- [ ] **Step 3: Plugar no AIService**

Em `app/Services/AIService.php`, método `generateContent()` (linha ~75): como PRIMEIRA instrução do método, antes de montar `$url`, inserir:

```php
        $tenant = auth()->user()?->tenant;
        AiQuota::assertAvailable($tenant);
```

E imediatamente APÓS a resposta bem-sucedida da API (no ponto onde a resposta é validada como ok, antes do `return` do texto), inserir:

```php
        AiQuota::record($tenant?->id);
```

Adicionar os imports no topo do arquivo:

```php
use App\Services\AiQuota;
```

(O import de `AiQuota` é same-namespace, então é opcional — usar diretamente `AiQuota::` já resolve. Não importar `Tenant`; o tipo vem da relação.)

- [ ] **Step 4: Tratamento digno nos consumidores**

Em `app/Http/Controllers/RAGController.php`, método `query()`: envolver a chamada que gera a resposta da IA num catch específico ANTES do catch genérico existente:

```php
        } catch (\App\Exceptions\AiQuotaExceededException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 429);
        }
```

Em `app/Services/MetadataInference.php`, no método `infer()`: garantir que a chamada à IA esteja em try/catch e que `AiQuotaExceededException` (como qualquer falha de IA) resulte em retorno `null`/campos vazios — o documento entra sem inferência e o coordenador preenche manualmente (spec §5). Se o método já tem try/catch genérico, nada a fazer; confirmar lendo o arquivo.

- [ ] **Step 5: Verificar sintaxe**

Run: `php -l app/Exceptions/AiQuotaExceededException.php; php -l app/Services/AiQuota.php; php -l app/Services/AIService.php; php -l app/Http/Controllers/RAGController.php; php -l app/Services/MetadataInference.php`
Expected: `No syntax errors detected` ×5

- [ ] **Step 6: Commit + push**

```bash
git add app/Exceptions/AiQuotaExceededException.php app/Services/AiQuota.php app/Services/AIService.php app/Http/Controllers/RAGController.php app/Services/MetadataInference.php
git commit -m "feat: contador mensal de uso de IA por tenant + cota preparada (null = ilimitado)"
git push origin main
```

- [ ] **Step 7: Smoke test em produção**

Fazer 1 pergunta à IANNE logado num tenant qualquer; no phpMyAdmin conferir: `SELECT * FROM ai_usage;` deve mostrar a linha do tenant no mês corrente com count ≥ 1.

---

### Task 4: TenantProvisioner + flag de turmas padrão

**Files:**
- Create: `app/Services/TenantProvisioner.php`
- Modify: `app/Models/School.php:22-39` (flag para pular turmas padrão)

**Interfaces:**
- Consumes: `App\Support\TempPassword::generate()`, models Tenant/School/User.
- Produces: `TenantProvisioner::provision(array $data): array` retornando `['tenant' => Tenant, 'credentials' => [['role' => string, 'name' => string, 'email' => string, 'password' => string], ...]]`. Task 5 consome exatamente esse shape.

- [ ] **Step 1: Flag no School model**

Em `app/Models/School.php`, adicionar a propriedade antes do `boot()` e a guarda dentro do evento:

```php
    /**
     * Quando true, o evento created NÃO cria as 30 turmas padrão (1º-5º Ano A-F).
     * Usado pelo TenantProvisioner para clientes de outras etapas de ensino.
     */
    public static bool $skipDefaultClasses = false;
```

E dentro de `static::created(function ($school) {`, como primeira linha:

```php
            if (static::$skipDefaultClasses) {
                return;
            }
```

- [ ] **Step 2: Criar o TenantProvisioner**

```php
<?php
// app/Services/TenantProvisioner.php

namespace App\Services;

use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TempPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Cria uma venda completa numa transação (spec §4): tenant → escola (quando o
 * plano tem) → usuários conforme o plano. Hoje chamado pela tela "Nova venda"
 * do SuperAdmin; no futuro, pelo webhook do gateway de pagamento.
 *
 * $data esperado:
 *  - tenant_name (string), plan ('coordenador'|'escola'|'semed'),
 *    billing_type ('mensal'|'vitalicio'), expires_at (date string|null),
 *    create_default_classes (bool)
 *  - school_name (string, planos coordenador/escola)
 *  - coordinator_name/coordinator_email (plano coordenador; opcionais no plano escola)
 *  - director_name/director_email (plano escola)
 *  - vice_name/vice_email (plano escola, opcionais)
 *  - semed_name/semed_email (plano semed)
 */
class TenantProvisioner
{
    public function provision(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $credentials = [];

            $tenant = Tenant::create([
                'name' => $data['tenant_name'],
                'slug' => $this->uniqueSlug($data['tenant_name']),
                'plan' => $data['plan'],
                'billing_type' => $data['billing_type'],
                'is_active' => true,
                'ai_enabled' => true,
                'max_schools_limit' => $data['plan'] === 'semed' ? 50 : 1,
                'expires_at' => $data['billing_type'] === 'vitalicio' ? null : $data['expires_at'],
            ]);

            $school = null;
            if (in_array($data['plan'], ['coordenador', 'escola'])) {
                School::$skipDefaultClasses = empty($data['create_default_classes']);
                $school = School::create([
                    'tenant_id' => $tenant->id,
                    'name' => $data['school_name'],
                ]);
                School::$skipDefaultClasses = false;
            }

            $mkUser = function (string $role, string $name, string $email) use ($tenant, $school, &$credentials) {
                $password = TempPassword::generate();
                User::create([
                    'tenant_id' => $tenant->id,
                    'school_id' => $school?->id,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => $role,
                ]);
                $credentials[] = compact('role', 'name', 'email', 'password');
            };

            if ($data['plan'] === 'coordenador') {
                $mkUser('coordinator', $data['coordinator_name'], $data['coordinator_email']);
            } elseif ($data['plan'] === 'escola') {
                $mkUser('director', $data['director_name'], $data['director_email']);
                if (!empty($data['vice_email'])) {
                    $mkUser('vice_director', $data['vice_name'], $data['vice_email']);
                }
                if (!empty($data['coordinator_email'])) {
                    $mkUser('coordinator', $data['coordinator_name'], $data['coordinator_email']);
                }
            } else { // semed
                $mkUser('semed', $data['semed_name'], $data['semed_email']);
            }

            return ['tenant' => $tenant, 'credentials' => $credentials];
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }
}
```

**Atenção (armadilha conhecida):** `User::create` dispara o `bootBelongsToTenant`, que só preenche `tenant_id` sozinho se vier vazio — aqui passamos explícito, então ok. O superadmin logado tem `tenant_id null`, por isso o preenchimento explícito é obrigatório.

- [ ] **Step 3: Verificar sintaxe**

Run: `php -l app/Services/TenantProvisioner.php; php -l app/Models/School.php`
Expected: `No syntax errors detected` ×2

- [ ] **Step 4: Commit + push**

```bash
git add app/Services/TenantProvisioner.php app/Models/School.php
git commit -m "feat: TenantProvisioner cria venda completa por plano em transacao"
git push origin main
```

---

### Task 5: Tela "Nova venda" no painel SuperAdmin

**Files:**
- Modify: `routes/web.php` (2 rotas novas no grupo `role:superadmin`)
- Modify: `app/Http/Controllers/SuperAdminController.php` (métodos `saleCreate` e `saleStore`; validação dos campos novos em `tenantsStore`/`tenantsUpdate`)
- Create: `resources/views/superadmin/sale_create.blade.php`
- Create: `resources/views/superadmin/sale_result.blade.php`
- Modify: `resources/views/layouts/sidebar-menu.blade.php:14` (link "Nova venda" no bloco superadmin)
- Modify: view de edição de tenant existente (`resources/views/superadmin/` — localizar o form de tenants e adicionar campos `plan`, `billing_type`, `ai_monthly_limit`)

**Interfaces:**
- Consumes: `TenantProvisioner::provision(array): array` (Task 4).
- Produces: rotas `superadmin.sale.create` (GET `/superadmin/vendas/nova`) e `superadmin.sale.store` (POST `/superadmin/vendas`).

- [ ] **Step 1: Rotas**

Em `routes/web.php`, dentro do grupo `role:superadmin` (após a linha da rota `superadmin.tenants.toggle`):

```php
        Route::get('/superadmin/vendas/nova', [SuperAdminController::class, 'saleCreate'])->name('superadmin.sale.create');
        Route::post('/superadmin/vendas', [SuperAdminController::class, 'saleStore'])->name('superadmin.sale.store');
```

- [ ] **Step 2: Métodos no SuperAdminController**

Adicionar ao `SuperAdminController` (import: `use App\Services\TenantProvisioner;`):

```php
    /** Assistente "Nova venda" (spec §4.2) */
    public function saleCreate()
    {
        return view('superadmin.sale_create');
    }

    public function saleStore(\Illuminate\Http\Request $request, TenantProvisioner $provisioner)
    {
        $validated = $request->validate([
            'tenant_name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:coordenador,escola,semed'],
            'billing_type' => ['required', 'in:mensal,vitalicio'],
            'expires_at' => ['required_if:billing_type,mensal', 'nullable', 'date'],
            'create_default_classes' => ['nullable', 'boolean'],
            'school_name' => ['required_unless:plan,semed', 'nullable', 'string', 'max:255'],
            'coordinator_name' => ['required_if:plan,coordenador', 'nullable', 'string', 'max:255'],
            'coordinator_email' => ['required_if:plan,coordenador', 'nullable', 'email', 'unique:users,email'],
            'director_name' => ['required_if:plan,escola', 'nullable', 'string', 'max:255'],
            'director_email' => ['required_if:plan,escola', 'nullable', 'email', 'unique:users,email'],
            'vice_name' => ['nullable', 'string', 'max:255'],
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
```

Nos métodos existentes `tenantsStore()` e `tenantsUpdate()`, adicionar às regras de validação:

```php
            'plan' => ['nullable', 'in:coordenador,escola,semed'],
            'billing_type' => ['nullable', 'in:mensal,vitalicio'],
            'ai_monthly_limit' => ['nullable', 'integer', 'min:1'],
```

- [ ] **Step 3: View do formulário**

Criar `resources/views/superadmin/sale_create.blade.php`. Seguir o padrão visual das views superadmin existentes (`<x-app-layout>`, card `bg-slate-900/60 border border-slate-800 rounded-xl`). Formulário Alpine com `x-data="{ plan: 'coordenador', billing: 'mensal' }"`:

```blade
<x-app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold text-slate-900">Nova Venda</h1></x-slot>

    <div class="max-w-2xl" x-data="{ plan: '{{ old('plan', 'coordenador') }}', billing: '{{ old('billing_type', 'mensal') }}' }">
        @if($errors->any())
            <div class="mb-4 p-4 bg-rose-100 text-rose-800 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.sale.store') }}" class="bg-slate-900/60 border border-slate-800 rounded-xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Nome do cliente (tenant)</label>
                <input type="text" name="tenant_name" value="{{ old('tenant_name') }}" required
                       class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100" placeholder="Ex: Coord. Maria — Escola Alfa / Prefeitura de ...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Plano</label>
                    <select name="plan" x-model="plan" class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        <option value="coordenador">Coordenador (individual)</option>
                        <option value="escola">Escola</option>
                        <option value="semed">SEMED (rede)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Cobrança</label>
                    <select name="billing_type" x-model="billing" class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        <option value="mensal">Mensal</option>
                        <option value="vitalicio">Vitalícia</option>
                    </select>
                </div>
            </div>

            <div x-show="billing === 'mensal'">
                <label class="block text-sm font-medium text-slate-300 mb-1">Vencimento</label>
                <input type="date" name="expires_at" value="{{ old('expires_at', now()->addDays(30)->format('Y-m-d')) }}"
                       class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
            </div>

            <template x-if="plan !== 'semed'">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nome da escola</label>
                        <input type="text" name="school_name" value="{{ old('school_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="create_default_classes" value="1" checked class="rounded bg-slate-800 border-slate-700">
                        Criar turmas padrão (1º–5º Ano, A–F)
                    </label>
                </div>
            </template>

            <template x-if="plan === 'coordenador' || plan === 'escola'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1" x-text="plan === 'coordenador' ? 'Nome do coordenador' : '1º coordenador (opcional)'"></label>
                        <input type="text" name="coordinator_name" value="{{ old('coordinator_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do coordenador</label>
                        <input type="email" name="coordinator_email" value="{{ old('coordinator_email') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                </div>
            </template>

            <template x-if="plan === 'escola'">
                <div class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Nome do diretor</label>
                            <input type="text" name="director_name" value="{{ old('director_name') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do diretor</label>
                            <input type="email" name="director_email" value="{{ old('director_email') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Vice-diretor (opcional)</label>
                            <input type="text" name="vice_name" value="{{ old('vice_name') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">E-mail do vice</label>
                            <input type="email" name="vice_email" value="{{ old('vice_email') }}"
                                   class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="plan === 'semed'">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Nome do usuário SEMED</label>
                        <input type="text" name="semed_name" value="{{ old('semed_name') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">E-mail SEMED</label>
                        <input type="email" name="semed_email" value="{{ old('semed_email') }}"
                               class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-100">
                    </div>
                </div>
            </template>

            <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-lg transition">
                Ativar venda
            </button>
        </form>
    </div>
</x-app-layout>
```

- [ ] **Step 4: View de resultado (credenciais, exibida uma única vez)**

Criar `resources/views/superadmin/sale_result.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header"><h1 class="text-lg font-semibold text-slate-900">Venda ativada ✅</h1></x-slot>

    <div class="max-w-2xl space-y-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 text-sm font-medium">
            Cliente <strong>{{ $tenant->name }}</strong> (plano {{ $tenant->plan }}, {{ $tenant->billing_type }}) criado.
            {{ $tenant->expires_at ? 'Vence em ' . $tenant->expires_at->format('d/m/Y') . '.' : 'Sem vencimento (vitalício).' }}
        </div>

        <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-6" x-data>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-slate-100 font-semibold">Credenciais de acesso</h2>
                <button @click="navigator.clipboard.writeText($refs.creds.innerText).then(() => $el.innerText = 'Copiado!')"
                        class="text-xs font-bold px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg">Copiar tudo</button>
            </div>
            <p class="text-xs text-amber-400 mb-3">⚠️ Esta tela é exibida uma única vez. Copie e envie ao cliente agora.</p>
            <div x-ref="creds" class="space-y-3 text-sm font-mono text-slate-200">
                <div>Acesso: {{ config('app.url') }}/login</div>
                @foreach($credentials as $cred)
                    <div class="border-t border-slate-800 pt-3">
                        <div>{{ ucfirst($cred['role']) }} — {{ $cred['name'] }}</div>
                        <div>E-mail: {{ $cred['email'] }}</div>
                        <div>Senha temporária: {{ $cred['password'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <a href="{{ route('superadmin.tenants') }}" class="inline-block text-sm text-indigo-600 hover:text-indigo-500 font-semibold">← Voltar aos clientes</a>
    </div>
</x-app-layout>
```

- [ ] **Step 5: Link no sidebar + campos no form de tenant**

Em `resources/views/layouts/sidebar-menu.blade.php`, dentro do bloco `@if($role === 'superadmin')`, ANTES do link "Municípios (SaaS)" (linha ~14), inserir:

```blade
        <a href="/superadmin/vendas/nova" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-lg transition duration-150 {{ request()->is('superadmin/vendas*') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/10' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Nova Venda</span>
        </a>
```

Localizar o formulário de editar tenant em `resources/views/superadmin/` (arquivo usado por `tenantsEdit`) e adicionar, junto aos campos existentes (`is_active`, `ai_enabled`, `expires_at`), seguindo o mesmo markup dos vizinhos:
- select `plan` (coordenador/escola/semed) com `selected` no valor atual;
- select `billing_type` (mensal/vitalicio, com opção vazia);
- input numérico `ai_monthly_limit` com placeholder "vazio = ilimitado".

- [ ] **Step 6: Verificar sintaxe + build**

Run: `php -l routes/web.php; php -l app/Http/Controllers/SuperAdminController.php`
Expected: `No syntax errors detected` ×2
Run: `npm run build`
Expected: build sem erros.

- [ ] **Step 7: Commit + push**

```bash
git add routes/web.php app/Http/Controllers/SuperAdminController.php resources/views/superadmin/sale_create.blade.php resources/views/superadmin/sale_result.blade.php resources/views/layouts/sidebar-menu.blade.php resources/views/superadmin public/build
git commit -m "feat: assistente Nova Venda no painel SuperAdmin (3 planos, credenciais copiaveis)"
git push origin main
```

- [ ] **Step 8: Smoke test em produção**

Logar como superadmin → "Nova Venda" → criar um cliente de teste plano coordenador (e-mail fictício). Conferir a tela de credenciais, logar com o coordenador criado em janela anônima, ver dashboard escolar e a tela de Correções. Depois excluir/desativar o tenant de teste no painel.

---

### Task 6: Desativar o /register público

**Files:**
- Modify: `routes/auth.php:14-18`
- Possibly modify: blades que linkam `route('register')` (localizar antes)

- [ ] **Step 1: Localizar referências**

Run: `grep -rn "route('register')" resources/views/`
Anotar cada arquivo retornado.

- [ ] **Step 2: Remover as rotas**

Em `routes/auth.php`, remover (ou comentar com nota) as linhas 15–18:

```php
    // Registro público desativado (spec camada comercial §7): contas são
    // criadas exclusivamente pelo provisionamento de vendas do SuperAdmin.
    // Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    // Route::post('register', [RegisteredUserController::class, 'store']);
```

- [ ] **Step 3: Remover os links encontrados no Step 1**

Em cada blade que referencia `route('register')` (tipicamente `auth/login.blade.php` e/ou `welcome.blade.php`), remover o link/botão de registro. **Obrigatório**, senão as páginas quebram com `RouteNotFoundException`.

- [ ] **Step 4: Verificar sintaxe + build**

Run: `php -l routes/auth.php`
Expected: `No syntax errors detected`
Run: `npm run build`
Expected: build sem erros.

- [ ] **Step 5: Commit + push**

```bash
git add routes/auth.php resources/views
git commit -m "feat: desativa registro publico (contas so via provisionamento de vendas)"
git push origin main
```

- [ ] **Step 6: Smoke test em produção**

Abrir `/register` → deve dar 404. Abrir `/login` e a homepage → sem erros.

---

## Self-Review (executado na escrita do plano)

- **Cobertura do spec:** §2 dados → Task 1; §3 middleware/banner/ai_enabled → Task 2; §5 cota → Task 3; §4 provisioner/tela → Tasks 4–5; §7 register → Task 6. Renovação (§2.1) usa tela existente + campos novos adicionados na Task 5 Step 5. Sem lacunas.
- **Placeholders:** nenhum TBD; os dois pontos "localizar arquivo" (form de tenant, links de register) têm comando/critério concreto de localização.
- **Consistência de tipos:** `provision(array): array{tenant, credentials}` idêntico entre Task 4 (produz) e Task 5 (consome); `isBlocked()` idêntico entre Task 1 e Task 2; `AiQuota::assertAvailable/record` idêntico entre Steps da Task 3.
