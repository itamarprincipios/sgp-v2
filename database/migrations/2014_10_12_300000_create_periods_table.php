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
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('bimester')->nullable();
            $table->dateTime('start_date');
            $table->date('end_date')->nullable();
            $table->dateTime('deadline');
            $table->dateTime('opening_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_physical_education')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};
