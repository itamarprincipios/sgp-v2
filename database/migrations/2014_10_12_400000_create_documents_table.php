<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('period_id')->constrained('periods')->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['planejamento', 'relatorio']);
            $table->string('file_path');
            $table->longText('content_text')->nullable();
            $table->timestamp('content_extracted_at')->nullable();
            $table->enum('status', ['pendente', 'enviado', 'atrasado', 'aprovado', 'rejeitado', 'ajustado'])->default('enviado');
            $table->text('feedback')->nullable();
            $table->decimal('score_base', 5, 2)->default(0.00);
            $table->decimal('penalty_delay', 5, 2)->default(0.00);
            $table->decimal('penalty_resubmission', 5, 2)->default(0.00);
            $table->decimal('score_final', 5, 2)->default(0.00);
            $table->integer('rejection_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
