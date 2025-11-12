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
        Schema::table('sales_goals', function (Blueprint $table) {
            // Tipo de comissão: 'goal' (por meta) ou 'sale' (por venda)
            $table->enum('commission_type', ['goal', 'sale'])
                ->default('goal')
                ->after('goal_amount')
                ->comment('Tipo de comissão: goal (por meta) ou sale (por venda)')
                ->nullable();

            // Porcentagem da comissão
            $table->decimal('commission_percentage', 5, 2)
                ->default(0.00)
                ->after('commission_type')
                ->comment('Porcentagem da comissão')
                ->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_goals', function (Blueprint $table) {
            $table->dropColumn('commission_percentage');
            $table->dropColumn('commission_type');
        });
    }
};
