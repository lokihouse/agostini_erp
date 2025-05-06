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
        Schema::create('work_slots', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Adiciona a chave estrangeira para a empresa
            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui work slots se a empresa for excluída

            $table->string('name'); // Nome do slot (ex: Bancada 1, Máquina A)
            $table->text('description')->nullable(); // Descrição (opcional)
            $table->string('location')->nullable(); // Localização (opcional)
            $table->boolean('is_active')->default(true)->index(); // Ativo/Inativo (indexado)
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // deleted_at (opcional)

            // Opcional: Adicionar um índice composto para otimizar buscas por empresa e nome
            // $table->index(['company_id', 'name']);

            // Opcional: Garantir que o nome do slot seja único DENTRO de cada empresa
            // $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_slots');
    }
};
