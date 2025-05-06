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
        Schema::create('production_logs', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Adiciona a chave estrangeira para a empresa
            // Redundante como no item, mas bom para consistência e queries diretas.
            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui logs se a empresa for excluída

            // Chaves estrangeiras principais
            $table->foreignUuid('production_order_item_uuid')
                ->constrained('production_order_items', 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_step_uuid')
                ->constrained('production_steps', 'uuid')
                ->restrictOnDelete(); // Mantido restrict

            $table->foreignUuid('work_slot_uuid')
                ->nullable()
                ->constrained('work_slots', 'uuid')
                ->nullOnDelete(); // Mantido nullOnDelete

            $table->foreignUuid('user_uuid')
                ->constrained('users', 'uuid')
                ->restrictOnDelete(); // Mantido restrict

            // Dados do registro
            $table->decimal('quantity', 15, 4);
            $table->dateTime('log_time')->useCurrent();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // --- Índices ---
            // Índice existente
            $table->index('log_time');

            // Opcional: Índices compostos para otimizar buscas por empresa
            // $table->index(['company_id', 'log_time']);
            // $table->index(['company_id', 'production_order_item_uuid']);
            // $table->index(['company_id', 'user_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
