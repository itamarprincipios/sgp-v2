<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Material de referência por município (modelo de requisitos, portaria, rubrica).
 *
 * O autodeploy da Hostinger NÃO roda migrations — o SQL equivalente está em
 * .agents/PIVO-STATUS.md e precisa ser executado no phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_reference_files')) {
            return;
        }

        Schema::create('tenant_reference_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('extension', 10);
            $table->longText('content_text')->nullable();
            $table->integer('chars')->default(0);
            $table->boolean('extraction_ok')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_reference_files');
    }
};
