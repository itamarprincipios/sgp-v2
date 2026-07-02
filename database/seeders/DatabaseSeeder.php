<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\School;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Inserir Inquilino (Tenant)
        DB::table('tenants')->insert([
            [
                'id' => 1,
                'name' => 'Prefeitura Exemplo',
                'slug' => 'exemplo',
                'is_active' => true,
                'ai_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 1. Inserir Escolas via Eloquent para disparar o evento que cria as 30 turmas padrão
        School::create([
            'id' => 1,
            'tenant_id' => 1,
            'name' => 'Escola Municipal Exemplo 1',
            'inep_code' => '12345678',
            'address' => 'Rua das Flores, 123',
        ]);

        School::create([
            'id' => 2,
            'tenant_id' => 1,
            'name' => 'Escola Municipal Exemplo 2',
            'inep_code' => '87654321',
            'address' => 'Av. Central, 456',
        ]);

        // 3. Inserir Usuários (Com senhas criptografadas usando Hash::make)
        DB::table('users')->insert([
            [
                'id' => 1,
                'tenant_id' => 1,
                'school_id' => null,
                'class_id' => null,
                'name' => 'Administrador do Sistema',
                'email' => 'admin@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'admin',
                'whatsapp' => '5595999999999',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tenant_id' => 1,
                'school_id' => null,
                'class_id' => null,
                'name' => 'Equipe SEMED',
                'email' => 'semed@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'semed',
                'whatsapp' => '5595988888888',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'tenant_id' => 1,
                'school_id' => 1,
                'class_id' => null,
                'name' => 'Coordenadora Milza (Escola 1)',
                'email' => 'coord1@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'coordinator',
                'whatsapp' => '5595977777777',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'tenant_id' => 1,
                'school_id' => 2,
                'class_id' => null,
                'name' => 'Coordenador Rosi (Escola 2)',
                'email' => 'coord2@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'coordinator',
                'whatsapp' => '5595966666666',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'tenant_id' => 1,
                'school_id' => 1,
                'class_id' => 1,
                'name' => 'Professor João da Silva',
                'email' => 'prof1@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'professor',
                'whatsapp' => '5595955555555',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'tenant_id' => 1,
                'school_id' => 1,
                'class_id' => 8,
                'name' => 'Professora Ana Santos',
                'email' => 'prof2@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'professor',
                'whatsapp' => '5595944444444',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'tenant_id' => 1,
                'school_id' => 1,
                'class_id' => 1,
                'name' => 'Professor Roberto (Ed. Física)',
                'email' => 'prof.edfis@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'professor',
                'whatsapp' => '5595933333333',
                'is_physical_education' => true,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'tenant_id' => 1,
                'school_id' => null,
                'class_id' => null,
                'name' => 'Supervisora Sandra (Ed. Física)',
                'email' => 'supervisor.edfis@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'supervisor_edfis',
                'whatsapp' => '5595922222222',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'tenant_id' => null,
                'school_id' => null,
                'class_id' => null,
                'name' => 'Super Administrador',
                'email' => 'superadmin@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'superadmin',
                'whatsapp' => '5595999999999',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'tenant_id' => 1,
                'school_id' => 1,
                'class_id' => null,
                'name' => 'Diretor Roberto Silva',
                'email' => 'diretor1@sgp.com',
                'password' => Hash::make('senha123'),
                'role' => 'director',
                'whatsapp' => '5595999991234',
                'is_physical_education' => false,
                'is_monitor' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Vincular coordenadores à tabela user_schools
        DB::table('user_schools')->insert([
            ['user_id' => 3, 'school_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 4, 'school_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => 10, 'school_id' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Inserir Períodos de Planejamento
        DB::table('periods')->insert([
            [
                'id' => 1,
                'tenant_id' => 1,
                'school_id' => null, // Período global para a rede
                'name' => 'Planejamento Junho 2026',
                'description' => 'Período regular de planejamento pedagógico da rede',
                'bimester' => 2,
                'start_date' => '2026-06-01 00:00:00',
                'end_date' => '2026-06-30 23:59:59',
                'deadline' => '2026-06-30 23:59:59',
                'opening_date' => '2026-06-01 00:00:00',
                'is_active' => true,
                'is_physical_education' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'tenant_id' => 1,
                'school_id' => 1, // Período específico da Escola 1
                'name' => 'Planejamento Julho 2026 (Escola 1)',
                'description' => 'Período de planejamento pedagógico para a Escola 1',
                'bimester' => 3,
                'start_date' => '2026-07-01 00:00:00',
                'end_date' => '2026-07-31 23:59:59',
                'deadline' => '2026-07-10 23:59:59',
                'opening_date' => '2026-06-20 00:00:00',
                'is_active' => true,
                'is_physical_education' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
