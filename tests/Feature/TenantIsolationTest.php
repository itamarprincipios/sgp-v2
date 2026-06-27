<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that models (like School and Period) are automatically filtered by the user's tenant_id.
     */
    public function test_models_are_automatically_filtered_by_tenant_id(): void
    {
        // 1. Criar dois Tenants
        $tenantA = Tenant::create(['name' => 'Prefeitura A', 'slug' => 'semed-a']);
        $tenantB = Tenant::create(['name' => 'Prefeitura B', 'slug' => 'semed-b']);

        // 2. Criar Escolas em cada Tenant
        $schoolA = School::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Escola de A',
            'inep_code' => '11111111',
        ]);

        $schoolB = School::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Escola de B',
            'inep_code' => '22222222',
        ]);

        // 3. Criar Períodos em cada Tenant
        $periodA = Period::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Período A',
            'bimester' => 1,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'deadline' => now()->addMonth(),
            'opening_date' => now(),
        ]);

        $periodB = Period::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Período B',
            'bimester' => 1,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'deadline' => now()->addMonth(),
            'opening_date' => now(),
        ]);

        // 4. Criar um usuário do Tenant A
        $userA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'role' => 'professor',
        ]);

        // 5. Sob a autenticação do usuário A, as consultas devem ser filtradas
        $this->actingAs($userA);

        // Deve enxergar apenas a escola e o período do Tenant A
        $schools = School::all();
        $periods = Period::all();

        $this->assertCount(1, $schools);
        $this->assertEquals('Escola de A', $schools->first()->name);

        $this->assertCount(1, $periods);
        $this->assertEquals('Período A', $periods->first()->name);
    }

    /**
     * Test that a superadmin user bypasses the TenantScope and can see everything.
     */
    public function test_superadmin_bypasses_tenant_scope_and_sees_everything(): void
    {
        $tenantA = Tenant::create(['name' => 'Prefeitura A', 'slug' => 'semed-a']);
        $tenantB = Tenant::create(['name' => 'Prefeitura B', 'slug' => 'semed-b']);

        School::create(['tenant_id' => $tenantA->id, 'name' => 'Escola de A', 'inep_code' => '11111111']);
        School::create(['tenant_id' => $tenantB->id, 'name' => 'Escola de B', 'inep_code' => '22222222']);

        // Criar usuário Super Admin (tenant_id = null)
        $superadmin = User::factory()->create([
            'tenant_id' => null,
            'role' => 'superadmin',
        ]);

        $this->actingAs($superadmin);

        // Super admin deve ver todas as escolas sem filtro de escopo
        $schools = School::all();
        $this->assertCount(2, $schools);
    }

    /**
     * Test that tenant_id is automatically injected when creating new models.
     */
    public function test_tenant_id_is_automatically_injected_on_creation(): void
    {
        $tenant = Tenant::create(['name' => 'Prefeitura de Teste', 'slug' => 'teste-semed']);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'coordinator',
        ]);

        $this->actingAs($user);

        // Cria uma escola sem especificar o tenant_id
        $school = School::create([
            'name' => 'Escola Sem Tenant ID Explícito',
            'inep_code' => '12341234',
        ]);

        // O tenant_id deve ter sido injetado automaticamente pelo BelongsToTenant Trait
        $this->assertEquals($tenant->id, $school->tenant_id);

        $this->assertDatabaseHas('schools', [
            'id' => $school->id,
            'tenant_id' => $tenant->id,
            'name' => 'Escola Sem Tenant ID Explícito',
        ]);
    }
}
