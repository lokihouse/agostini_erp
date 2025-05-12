<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('sales_order_id')->constrained('sales_orders', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products', 'uuid')->restrictOnDelete(); // Restringe deleção do produto se estiver em um pedido

            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2)->comment('Preço unitário do produto no momento da venda');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Valor do desconto aplicado ao item');
            $table->decimal('final_price', 15, 2)->comment('Preço final do item (unit_price - discount_amount)');
            $table->decimal('total_price', 15, 2)->comment('Preço total do item (quantity * final_price)');

            $table->text('notes')->nullable();
            $table->timestamps();
            // Não costuma ter softDeletes para itens de pedido, mas pode adicionar se necessário

            $table->unique(['sales_order_id', 'product_id']); // Um produto por pedido
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
