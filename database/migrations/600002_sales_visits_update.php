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
        Schema::table('sales_visits', function (Blueprint $table) {
            // Adiciona a restrição de chave estrangeira à coluna sales_order_id existente
            $table->foreign('sales_order_id') // Nome da coluna em sales_visits
            ->references('uuid')       // Coluna que referencia em sales_orders
            ->on('sales_orders')       // Tabela que referencia
            ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_visits', function (Blueprint $table) {
            // O nome da constraint geralmente é 'nome_tabela_nome_coluna_foreign'
            $table->dropForeign(['sales_order_id']); // Ou $table->dropForeign('sales_visits_sales_order_id_foreign');
        });
    }
};
