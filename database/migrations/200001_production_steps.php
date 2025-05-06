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
        Schema::create('production_steps', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Adiciona a chave estrangeira para a empresa
            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui etapas se a empresa for excluída

            $table->string('name'); // Nome da etapa (ex: Corte, Montagem)
            $table->text('description')->nullable(); // Descrição (opcional)
            $table->integer('default_order')->nullable()->index(); // Ordem padrão (opcional, indexado para buscas)
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // deleted_at (opcional)

            // Opcional: Adicionar um índice composto para otimizar buscas por empresa e nome
            // $table->index(['company_id', 'name']);

            // Opcional: Garantir que o nome da etapa seja único DENTRO de cada empresa
            // $table->unique(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_steps');
    }
};
