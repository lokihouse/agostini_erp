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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Adiciona a chave estrangeira para a empresa
            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui ordens se a empresa for excluída

            // Número da ordem - DEVE ser único DENTRO de cada empresa
            $table->string('order_number'); // Remove unique() daqui

            $table->date('due_date')->nullable()->index(); // Data limite (opcional, indexada)
            $table->dateTime('start_date')->nullable(); // Data/hora de início (opcional)
            $table->dateTime('completion_date')->nullable(); // Data/hora de conclusão (opcional)
            $table->string('status')->default('Pendente')->index(); // Status (ex: Pendente, Em Andamento, Concluída, Cancelada) - indexado
            $table->text('notes')->nullable(); // Observações gerais (opcional)

            // Chave estrangeira para o usuário que criou/é responsável
            // O usuário já pertence a uma empresa, então a ligação está implícita
            $table->foreignUuid('user_uuid')->nullable()->constrained('users', 'uuid')->nullOnDelete();

            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // deleted_at (opcional)

            // --- Índices e Constraints ---
            // Garante que o order_number seja único por empresa
            $table->unique(['company_id', 'order_number']);

            // Opcional: Índice composto para otimizar buscas por empresa e status/data
            // $table->index(['company_id', 'status']);
            // $table->index(['company_id', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
