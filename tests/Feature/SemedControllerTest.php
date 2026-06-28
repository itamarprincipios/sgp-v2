<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SemedControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $school;
    protected $semedUser;
    protected $coordinator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = \App\Models\Tenant::create([
            'name' => 'Prefeitura Teste',
            'slug' => 'teste',
            'is_active' => true,
        ]);

        $this->school = School::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Escola Municipal Teste',
            'inep_code' => '12345678',
        ]);

        $this->semedUser = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Equipe SEMED Teste',
            'email' => 'semed.teste@sgp.com',
            'password' => bcrypt('senha123'),
            'role' => 'semed',
        ]);

        $this->coordinator = User::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Coordenador Teste',
            'email' => 'coord.teste@sgp.com',
            'password' => bcrypt('senha123'),
            'role' => 'coordinator',
        ]);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('semed.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_coordinator_cannot_access_semed_dashboard()
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('semed.dashboard'));

        $response->assertStatus(403);
    }

    public function test_semed_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->semedUser)
            ->get(route('semed.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Painel da Secretaria (SEMED)');
        $response->assertSee('Ranking de Escolas mais Pontuais');
        $response->assertSee('Professores Destaque');
        $response->assertSee('Coordenadores Destaque');
    }

    public function test_semed_user_can_view_security_page()
    {
        $response = $this->actingAs($this->semedUser)
            ->get(route('semed.security'));

        $response->assertStatus(200);
        $response->assertSee('Segurança da Conta');
    }

    public function test_semed_user_can_update_own_password()
    {
        $response = $this->actingAs($this->semedUser)->put(route('semed.security.password'), [
            'current_password' => 'senha123',
            'password' => 'novaSenhaForte123',
            'password_confirmation' => 'novaSenhaForte123',
        ]);

        $response->assertRedirect(route('semed.security'));
        $response->assertSessionHas('success');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('novaSenhaForte123', $this->semedUser->refresh()->password));
    }

    public function test_semed_password_update_requires_correct_current_password()
    {
        $response = $this->actingAs($this->semedUser)->put(route('semed.security.password'), [
            'current_password' => 'senha-errada',
            'password' => 'novaSenhaForte123',
            'password_confirmation' => 'novaSenhaForte123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('senha123', $this->semedUser->refresh()->password));
    }
}
