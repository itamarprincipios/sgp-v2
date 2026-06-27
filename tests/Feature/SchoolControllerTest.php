<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $school;
    protected $director;
    protected $coordinator;
    protected $professor;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic tenant and school
        $this->tenant = \App\Models\Tenant::create([
            'name' => 'Prefeitura Teste',
            'slug' => 'teste',
            'is_active' => true,
        ]);

        $this->school = School::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Escola Teste',
            'inep_code' => '99999999',
        ]);

        $this->director = User::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Diretor Teste',
            'email' => 'dir.teste@sgp.com',
            'password' => bcrypt('senha123'),
            'role' => 'director',
        ]);

        $this->coordinator = User::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Coordenador Teste',
            'email' => 'coord.teste@sgp.com',
            'password' => bcrypt('senha123'),
            'role' => 'coordinator',
        ]);

        $this->professor = User::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Professor Teste',
            'email' => 'prof.teste@sgp.com',
            'password' => bcrypt('senha123'),
            'role' => 'professor',
        ]);

        // Link in pivot table
        \Illuminate\Support\Facades\DB::table('user_schools')->insert([
            ['user_id' => $this->director->id, 'school_id' => $this->school->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->coordinator->id, 'school_id' => $this->school->id, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('school.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_professor_cannot_access_school_dashboard()
    {
        $response = $this->actingAs($this->professor)
            ->get(route('school.dashboard'));

        $response->assertStatus(403);
    }

    public function test_coordinator_can_access_school_dashboard()
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('school.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Painel da Escola');
        $response->assertSee($this->school->name);
    }

    public function test_coordinator_can_access_plannings_page()
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('school.plannings'));

        $response->assertStatus(200);
        $response->assertSee('Cronogramas de Planejamento');
    }

    public function test_coordinator_can_access_professors_page()
    {
        $response = $this->actingAs($this->coordinator)
            ->get(route('school.professors'));

        $response->assertStatus(200);
        $response->assertSee('Gerenciamento de Professores');
    }

    public function test_coordinator_can_view_planning_details()
    {
        $period = Period::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Planejamento Teste',
            'description' => 'Desc',
            'bimester' => 2,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'deadline' => now()->addDays(5),
            'opening_date' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->coordinator)
            ->get(route('school.planning.view', ['id' => $period->id]));

        $response->assertStatus(200);
        $response->assertSee('Detalhes do Planejamento');
        $response->assertSee($period->name);
    }

    public function test_coordinator_can_review_document()
    {
        $period = Period::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Planejamento Teste',
            'description' => 'Desc',
            'bimester' => 2,
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'deadline' => now()->addDays(5),
            'opening_date' => now(),
            'is_active' => true,
        ]);

        $doc = \App\Models\Document::create([
            'tenant_id' => $this->tenant->id,
            'period_id' => $period->id,
            'user_id' => $this->professor->id,
            'title' => 'Planejamento de Teste',
            'type' => 'planejamento',
            'file_path' => 'test.docx',
            'status' => 'enviado',
            'score_base' => 10.00,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->coordinator)
            ->post(route('school.document.review'), [
                'id' => $doc->id,
                'status' => 'aprovado',
                'feedback' => 'Muito bom!',
            ]);

        $response->assertRedirect(route('school.planning.view', ['id' => $period->id]));
        $this->assertEquals('aprovado', $doc->fresh()->status);
        $this->assertEquals('Muito bom!', $doc->fresh()->feedback);
    }

    public function test_director_can_manage_coordinators()
    {
        // 1. Store
        $response = $this->actingAs($this->director)
            ->post(route('school.coordinator.store'), [
                'school_id' => $this->school->id,
                'name' => 'Novo Coordenador',
                'email' => 'novo.coord@sgp.com',
                'whatsapp' => '5595999999999',
            ]);

        $response->assertRedirect(route('school.dashboard', ['tab' => 'coordinators']));
        
        $newCoord = User::where('email', 'novo.coord@sgp.com')->first();
        $this->assertNotNull($newCoord);
        $this->assertEquals('coordinator', $newCoord->role);

        // 2. Reset password
        $response2 = $this->actingAs($this->director)
            ->post(route('school.coordinator.reset-password'), [
                'id' => $newCoord->id,
            ]);

        $response2->assertRedirect(route('school.dashboard', ['tab' => 'coordinators']));

        // 3. Delete
        $response3 = $this->actingAs($this->director)
            ->delete(route('school.coordinator.delete'), [
                'id' => $newCoord->id,
            ]);

        $response3->assertRedirect(route('school.dashboard', ['tab' => 'coordinators']));
        $this->assertNull($newCoord->fresh());
    }

    public function test_coordinator_cannot_manage_coordinators()
    {
        $response = $this->actingAs($this->coordinator)
            ->post(route('school.coordinator.store'), [
                'school_id' => $this->school->id,
                'name' => 'Novo Coordenador 2',
                'email' => 'novo.coord2@sgp.com',
            ]);

        $response->assertStatus(403);
    }

    public function test_coordinator_can_manage_planning_periods()
    {
        $period = Period::create([
            'tenant_id' => $this->tenant->id,
            'school_id' => $this->school->id,
            'name' => 'Original Period',
            'description' => 'Original Description',
            'bimester' => 2,
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'deadline' => now()->addDays(5),
            'opening_date' => now(),
            'is_active' => true,
        ]);

        // 1. Edit
        $response1 = $this->actingAs($this->coordinator)
            ->get(route('school.planning.edit', ['id' => $period->id]));
        $response1->assertStatus(200);
        $response1->assertSee('Editar Período de Planejamento');

        // 2. Update
        $response2 = $this->actingAs($this->coordinator)
            ->put(route('school.planning.update'), [
                'id' => $period->id,
                'school_id' => $this->school->id,
                'name' => 'Updated Period',
                'description' => 'Updated Description',
                'start_date' => now()->addDays(10)->format('Y-m-d'),
            ]);
        $response2->assertRedirect(route('school.plannings'));
        $this->assertEquals('Updated Period', $period->fresh()->name);

        // 3. Delete
        $response3 = $this->actingAs($this->coordinator)
            ->delete(route('school.planning.delete'), [
                'id' => $period->id,
            ]);
        $response3->assertRedirect(route('school.plannings'));
        $this->assertNull($period->fresh());
    }
}
