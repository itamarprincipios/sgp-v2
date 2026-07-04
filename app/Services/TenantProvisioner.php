<?php

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
