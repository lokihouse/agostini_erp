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
        Schema::table('sales_orders', function (Blueprint $table) {
            // Valor da comissão gerada por este pedido
            $table->decimal('commission_amount', 15, 2)
                ->default(0.00)
                ->after('total_amount')
                ->comment('Valor da comissão gerada por este pedido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('commission_amount');
        });
    }
};
