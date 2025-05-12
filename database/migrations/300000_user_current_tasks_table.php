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
                ->nullable()
                ->constrained('work_slots', 'uuid')
                ->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_resumed_at')->nullable();
            $table->timestamp('last_pause_at')->nullable();
            $table->foreignUuid('last_pause_reason_uuid')
                ->nullable()
                ->constrained('pause_reasons', 'uuid')
                ->nullOnDelete();
            $table->unsignedBigInteger('total_active_seconds')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['user_uuid', 'status']);
            $table->unique(['user_uuid', 'status'], 'user_single_active_or_paused_task')
                ->whereIn('status', ['active', 'paused']);
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
