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
        Schema::create('user_current_tasks', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Chaves estrangeiras
            $table->foreignUuid('user_uuid')
                ->constrained('users', 'uuid') // Liga com users.uuid
                ->cascadeOnDelete(); // Se o usuário for deletado, remove a tarefa atual

            $table->foreignUuid('production_order_item_uuid')
                ->constrained('production_order_items', 'uuid') // Liga com production_order_items.uuid
                ->cascadeOnDelete(); // Se o item for deletado, remove a tarefa atual

            $table->foreignUuid('production_step_uuid')
                ->constrained('production_steps', 'uuid') // Liga com production_steps.uuid
                ->cascadeOnDelete(); // Se a etapa for deletada, remove a tarefa atual

            $table->foreignUuid('work_slot_uuid')
                ->nullable() // Opcional: onde o usuário está trabalhando
                ->constrained('work_slots', 'uuid') // Liga com work_slots.uuid
                ->nullOnDelete(); // Se o slot for deletado, apenas define como null aqui

            // Status da tarefa para este usuário
            $table->string('status')->default('active')->index(); // Ex: active, paused

            // Timestamps da sessão de trabalho
            $table->timestamp('started_at')->nullable(); // Quando o trabalho nesta tarefa/etapa começou
            $table->timestamp('last_resumed_at')->nullable(); // Quando foi retomado após a última pausa
            $table->timestamp('last_pause_at')->nullable(); // Quando a última pausa começou
            $table->string('last_pause_reason')->nullable(); // Motivo da última pausa

            // Tempo acumulado (em segundos)
            $table->unsignedBigInteger('total_active_seconds')->default(0); // Tempo total ativo (excluindo pausas)

            $table->text('notes')->nullable(); // Notas específicas desta sessão

            $table->timestamps(); // created_at e updated_at

            // Índices para otimização
            $table->index(['user_uuid', 'status']);
            // Garante que um usuário só pode ter uma tarefa ativa por vez
            $table->unique(['user_uuid', 'status'], 'user_single_active_task')
                ->where('status', 'active'); // A restrição unique só se aplica se status='active'
            // Nota: Alguns bancos (como SQLite < 3.35.0) podem não suportar `where` em unique constraints.
            // Se for o caso, a lógica de controle precisará ser feita na aplicação.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_current_tasks');
    }
};
