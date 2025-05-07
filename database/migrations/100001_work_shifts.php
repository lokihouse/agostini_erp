<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Company; // Import Company model

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->string('name'); // Ex: "Turno Administrativo", "Escala 12x36 Noturna"
            $table->enum('type', ['weekly', 'cyclical'])->comment('Tipo de jornada: semanal ou cíclica');
            $table->text('notes')->nullable();

            // Campos para o tipo 'cyclical' (nullable se for 'weekly')
            $table->unsignedInteger('cycle_work_duration_hours')->nullable()->comment('Horas trabalhadas no período "on" do ciclo. Ex: 12 para 12x36');
            $table->unsignedInteger('cycle_off_duration_hours')->nullable()->comment('Horas de folga antes da repetição do ciclo. Ex: 36 para 12x36');
            $table->time('cycle_shift_starts_at')->nullable()->comment('Hora de início do turno em um dia de trabalho do ciclo');
            $table->time('cycle_shift_ends_at')->nullable()->comment('Hora de término do turno em um dia de trabalho do ciclo');
            $table->time('cycle_interval_starts_at')->nullable()->comment('Hora de início do intervalo no ciclo');
            $table->time('cycle_interval_ends_at')->nullable()->comment('Hora de término do intervalo no ciclo');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
