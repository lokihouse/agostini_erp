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
        Schema::create('production_step_work_slot', function (Blueprint $table) {
            $table->id(); // Ou $table->uuid('uuid')->primary();

            // Chaves estrangeiras
            $table->foreignUuid('production_step_uuid')->constrained('production_steps', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('work_slot_uuid')->constrained('work_slots', 'uuid')->cascadeOnDelete();

            $table->timestamps(); // Opcional

            // Garantir que a combinação etapa/slot seja única (opcional, mas recomendado)
            $table->unique(['production_step_uuid', 'work_slot_uuid'], 'step_slot_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_step_work_slot');
    }
};

