<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_order_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained(table: 'companies', column: 'uuid')->cascadeOnDelete();
            $table->foreignUuid('transport_order_id')->constrained(table: 'transport_orders', column: 'uuid')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained(table: 'clients', column: 'uuid')->comment('Cliente para esta entrega');
            $table->foreignUuid('product_id')->constrained(table: 'products', column: 'uuid')->comment('Produto a ser entregue');
            $table->foreignUuid('sales_order_item_id')->nullable()->constrained(table: 'sales_order_items', column: 'uuid')->nullOnDelete()->comment('Item do Pedido de Venda (opcional)');

            $table->decimal('quantity', 15, 4);
            $table->text('delivery_address_snapshot')->nullable()->comment('Endereço de entrega no momento da criação (pode ser JSON ou texto formatado)');

            $table->enum('status', ['pending', 'completed', 'returned'])->default('pending')->comment('Status desta entrega específica');
            $table->integer('delivery_sequence')->nullable()->comment('Ordem da entrega na rota');

            $table->json('delivery_photos')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->foreignUuid('processed_by_user_id')->nullable()->constrained(table: 'users', column: 'uuid')->nullOnDelete()->comment('Usuário que marcou como entregue/retornado');

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['transport_order_id', 'client_id', 'product_id'], 'transport_item_client_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_order_items');
    }
};
