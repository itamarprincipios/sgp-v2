<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests (unauthenticated users) are redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/superadmin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/superadmin/tenants');
        $response->assertRedirect('/login');
    }

    /**
     * Test that standard users (e.g., professor, coordinator) cannot access superadmin routes.
     */
    public function test_regular_user_cannot_access_superadmin_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'professor',
        ]);

        $response = $this->actingAs($user)->get('/superadmin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/superadmin/tenants');
        $response->assertStatus(403);
    }

    /**
     * Test that a superadmin can access the dashboard.
     */
    public function test_superadmin_can_access_dashboard(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $response = $this->actingAs($superadmin)->get('/superadmin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Painel SaaS - Visão Global');
        $response->assertSee('Municípios (Inquilinos)');
    }

    /**
     * Test that a superadmin can view tenants list.
     */
    public function test_superadmin_can_view_tenants_list(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $tenant = Tenant::create([
            'name' => 'Prefeitura Exemplo Boa Vista',
            'slug' => 'boavista-semed',
            'is_active' => true,
            'ai_enabled' => true,
            'max_schools_limit' => 15,
        ]);

        $response = $this->actingAs($superadmin)->get('/superadmin/tenants');

        $response->assertStatus(200);
        $response->assertSee('Prefeitura Exemplo Boa Vista');
        $response->assertSee('boavista-semed');
    }

    /**
     * Test that a superadmin can store a new tenant.
     */
    public function test_superadmin_can_store_new_tenant(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $tenantData = [
            'name' => 'Prefeitura de Caracaraí',
            'slug' => 'caracarai',
            'max_schools_limit' => 8,
            'is_active' => '1',
            'ai_enabled' => '1',
            'expires_at' => '2027-12-31',
        ];

        $response = $this->actingAs($superadmin)->post('/superadmin/tenants', $tenantData);

        $response->assertRedirect('/superadmin/tenants');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Prefeitura de Caracaraí',
            'slug' => 'caracarai',
            'max_schools_limit' => 8,
            'is_active' => true,
            'ai_enabled' => true,
        ]);
    }

    /**
     * Test that tenant validation prevents duplicate slugs.
     */
    public function test_tenant_creation_prevents_duplicate_slugs(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        Tenant::create([
            'name' => 'Prefeitura 1',
            'slug' => 'prefeitura-duplicada',
            'max_schools_limit' => 10,
        ]);

        $tenantData = [
            'name' => 'Prefeitura 2',
            'slug' => 'prefeitura-duplicada',
            'max_schools_limit' => 5,
        ];

        $response = $this->actingAs($superadmin)->post('/superadmin/tenants', $tenantData);

        $response->assertSessionHasErrors('slug');
    }

    /**
     * Test that a superadmin can view edit form and update tenant.
     */
    public function test_superadmin_can_edit_and_update_tenant(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $tenant = Tenant::create([
            'name' => 'Prefeitura Antiga',
            'slug' => 'pref-antiga',
            'max_schools_limit' => 5,
            'is_active' => true,
            'ai_enabled' => false,
        ]);

        $editUrl = "/superadmin/tenants/{$tenant->id}/edit";
        $updateUrl = "/superadmin/tenants/{$tenant->id}";

        $response = $this->actingAs($superadmin)->get($editUrl);
        $response->assertStatus(200);
        $response->assertSee('Prefeitura Antiga');

        $updateData = [
            'name' => 'Prefeitura Atualizada',
            'slug' => 'pref-atualizada',
            'max_schools_limit' => 12,
            'is_active' => '1',
            'ai_enabled' => '1',
        ];

        $response = $this->actingAs($superadmin)->put($updateUrl, $updateData);

        $response->assertRedirect('/superadmin/tenants');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Prefeitura Atualizada',
            'slug' => 'pref-atualizada',
            'max_schools_limit' => 12,
            'is_active' => true,
            'ai_enabled' => true,
        ]);
    }

    /**
     * Test that superadmin can toggle tenant status.
     */
    public function test_superadmin_can_toggle_tenant_status(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
        ]);

        $tenant = Tenant::create([
            'name' => 'Prefeitura Ativa',
            'slug' => 'pref-ativa',
            'max_schools_limit' => 5,
            'is_active' => true,
        ]);

        $toggleUrl = "/superadmin/tenants/{$tenant->id}/toggle";

        // Toggle to inactive
        $response = $this->actingAs($superadmin)->patch($toggleUrl);
        $response->assertRedirect();
        $this->assertFalse($tenant->refresh()->is_active);

        // Toggle back to active
        $response = $this->actingAs($superadmin)->patch($toggleUrl);
        $response->assertRedirect();
        $this->assertTrue($tenant->refresh()->is_active);
    }
}
