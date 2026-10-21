<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ampliação do escopo da IANNE: a Central de Correção passa a receber, pelo
 * mesmo campo de envio, planejamento, PEI, relatório do professor, relatório de
 * aluno da educação especial e relatório de educação infantil.
 *
 * `doc_kind` guarda esse tipo fino (catálogo em App\Services\DocumentKinds).
 * A coluna antiga `type` continua existindo e recebendo o balde correspondente
 * (planejamento/relatorio/outro) para não quebrar telas e relatórios legados —
 * por isso aqui NÃO se mexe no enum dela.
 *
 * `student_name` é o aluno/criança de quem o documento trata (PEI e relatórios
 * individuais); fica vazio em planejamento e relatório de turma.
 *
 * Documentos anteriores a esta migração ficam com doc_kind NULL e são tratados
 * pelo `type` (DocumentKinds::resolve), sem necessidade de backfill.
 *
 * SQL equivalente precisa ser executado no phpMyAdmin de produção — o
 * autodeploy da Hostinger não roda migrations (ver PIVO-STATUS.md).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'doc_kind')) {
                $table->string('doc_kind', 40)->nullable()->after('type');
            }
            if (!Schema::hasColumn('documents', 'student_name')) {
                $table->string('student_name')->nullable()->after('discipline');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            foreach (['doc_kind', 'student_name'] as $coluna) {
                if (Schema::hasColumn('documents', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};
