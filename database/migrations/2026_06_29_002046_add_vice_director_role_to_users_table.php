<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $oldRoles = [
        'superadmin', 'admin', 'semed', 'director', 'coordinator',
        'professor', 'supervisor_edfis', 'supervisor_monitor',
        'supervisor_infantil', 'supervisor_fundamental',
    ];

    private array $newRoles = [
        'superadmin', 'admin', 'semed', 'director', 'vice_director', 'coordinator',
        'professor', 'supervisor_edfis', 'supervisor_monitor',
        'supervisor_infantil', 'supervisor_fundamental',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enum = implode(',', array_map(fn ($r) => "'{$r}'", $this->newRoles));
        DB::statement("ALTER TABLE users MODIFY role ENUM({$enum}) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $enum = implode(',', array_map(fn ($r) => "'{$r}'", $this->oldRoles));
        DB::statement("ALTER TABLE users MODIFY role ENUM({$enum}) NOT NULL");
    }
};
