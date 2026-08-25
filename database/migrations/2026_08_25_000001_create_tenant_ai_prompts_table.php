<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blocos de prompt da IANNE personalizados por município.
 *
 * O autodeploy da Hostinger NÃO roda migrations — o SQL equivalente está em
 * .agents/PIVO-STATUS.md e precisa ser executado no phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_ai_prompts')) {
            return;
        }

        Schema::create('tenant_ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('blocks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_ai_prompts');
    }
};
