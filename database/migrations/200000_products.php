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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            // Adiciona a chave estrangeira para a empresa
            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui produtos se a empresa for excluída

            $table->string('name'); // Nome do produto
            $table->string('sku')->unique()->nullable(); // Código único (opcional)
            $table->text('description')->nullable(); // Descrição (opcional)
            $table->string('unit_of_measure')->default('unidade'); // Unidade de medida (ex: peça, kg, litro)
            $table->decimal('standard_cost', 8, 2)->nullable();
            $table->decimal('sale_price', 8, 2)->nullable();
            $table->decimal('minimum_sale_price', 8, 2)->nullable();
            $table->timestamps(); // created_at e updated_at
            $table->softDeletes(); // deleted_at (para exclusão lógica)

            // Opcional: Adicionar um índice composto para otimizar buscas por empresa e SKU/nome
            // $table->index(['company_id', 'sku']);
            // $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // O Schema::dropIfExists já lida com a remoção da tabela e suas constraints
        Schema::dropIfExists('products');
    }
};
