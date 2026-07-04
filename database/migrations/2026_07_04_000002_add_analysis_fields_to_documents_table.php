<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2 do pivô — correção assistida. Guarda o parecer da IANNE (markdown,
 * editável pelo coordenador) no próprio documento.
 * SQL equivalente já executado em produção via phpMyAdmin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'analysis')) {
                $table->longText('analysis')->nullable()->after('reference_label');
            }
            if (!Schema::hasColumn('documents', 'analyzed_at')) {
                $table->timestamp('analyzed_at')->nullable()->after('analysis');
            }
        });
    }

    public function down(): void
    {
        // Sem rollback automático: o parecer é conteúdo do coordenador.
    }
};
