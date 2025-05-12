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
        Schema::create('task_pause_logs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            // Chave estrangeira para a tarefa atual do usuário que foi pausada
            $table->foreignUuid('user_current_task_uuid')
                ->constrained('user_current_tasks', 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_order_item_uuid')
                ->constrained('production_order_items', 'uuid')
                ->cascadeOnDelete();

            // Chave estrangeira para o motivo da pausa
            $table->foreignUuid('pause_reason_uuid')
                ->constrained('pause_reasons', 'uuid')
                ->restrictOnDelete(); // Não permite deletar um motivo se estiver em uso

            // Chave estrangeira para o usuário (redundante, mas pode ser útil para queries diretas)
            $table->foreignUuid('user_uuid')
                ->constrained('users', 'uuid')
                ->cascadeOnDelete();

            // Timestamps da pausa
            $table->timestamp('paused_at');         // Quando a pausa começou
            $table->timestamp('resumed_at')->nullable(); // Quando a pausa terminou (tarefa retomada)
            $table->unsignedInteger('duration_seconds')->nullable(); // Duração da pausa em segundos

            // Informações adicionais
            $table->decimal('quantity_produced_during_pause', 15, 4)->nullable()->comment('Qtd produzida antes de efetivamente pausar, se aplicável');
            $table->text('notes')->nullable(); // Observações sobre esta pausa específica

            $table->timestamps(); // created_at e updated_at para o registro do log
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_pause_logs');
    }
};
