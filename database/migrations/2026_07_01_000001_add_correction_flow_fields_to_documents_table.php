<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pivô "Plataforma de Correção" (AGENTS.md seção 14).
 * O documento passa a ser subido pelo coordenador (uploaded_by) em nome de um
 * professor cadastrado (user_id, agora nullable até a confirmação dos metadados).
 * O vínculo obrigatório com cronograma (period_id) é substituído por um rótulo
 * livre de período (reference_label).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Enums e nullability via SQL bruto (evita dependência do doctrine/dbal)
        DB::statement("ALTER TABLE documents MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE documents MODIFY period_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE documents MODIFY status ENUM('pendente','enviado','atrasado','aprovado','rejeitado','ajustado','aguardando_confirmacao','em_correcao','corrigido') NOT NULL DEFAULT 'enviado'");
        DB::statement("ALTER TABLE documents MODIFY type ENUM('planejamento','relatorio','outro') NOT NULL");

        Schema::table('documents', function ($table) {
            $table->foreignId('uploaded_by')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->after('uploaded_by')
                ->constrained('schools')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->after('period_id')
                ->constrained('classes')->nullOnDelete();
            $table->string('discipline', 100)->nullable()->after('title');
            $table->string('reference_label', 100)->nullable()->after('discipline');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function ($table) {
            $table->dropConstrainedForeignId('uploaded_by');
            $table->dropConstrainedForeignId('school_id');
            $table->dropConstrainedForeignId('class_id');
            $table->dropColumn(['discipline', 'reference_label']);
        });

        DB::statement("ALTER TABLE documents MODIFY type ENUM('planejamento','relatorio') NOT NULL");
        DB::statement("ALTER TABLE documents MODIFY status ENUM('pendente','enviado','atrasado','aprovado','rejeitado','ajustado') NOT NULL DEFAULT 'enviado'");
        DB::statement("ALTER TABLE documents MODIFY period_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE documents MODIFY user_id BIGINT UNSIGNED NOT NULL");
    }
};
