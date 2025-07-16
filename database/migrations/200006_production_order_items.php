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
        Schema::create('production_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid') // Vincula à tabela 'companies'
                ->cascadeOnDelete(); // Exclui itens se a empresa for excluída

            // Chaves estrangeiras
            $table->foreignUuid('production_order_uuid')
                ->constrained('production_orders', 'uuid') // Liga com production_orders.uuid
                ->cascadeOnDelete(); // Se a ordem for deletada, seus itens também são

            $table->foreignUuid('product_uuid')
                ->constrained('products', 'uuid') // Liga com products.uuid
                ->cascadeOnDelete(); // Se o produto for deletado, os itens da ordem relacionados a ele também são

            // Campos específicos deste item na ordem
            $table->decimal('quantity_planned', 15, 4)->default(0);
            $table->decimal('quantity_produced', 15, 4)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_items');
    }
};
