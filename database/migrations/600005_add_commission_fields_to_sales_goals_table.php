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
            // Altera a coluna existente em vez de recriar
            $table->enum('commission_type', ['none', 'goal', 'sale'])
                ->default('none')
                ->nullable()
                ->comment('Tipo de comissão: none (sem), goal (por meta) ou sale (por venda)')
                ->change();

            // Altera a porcentagem de comissão (se já existir)
            $table->decimal('commission_percentage', 5, 2)
                ->default(0.00)
                ->nullable()
                ->comment('Porcentagem da comissão')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_goals', function (Blueprint $table) {
            // Volta para os valores anteriores, se desejar
            $table->enum('commission_type', ['goal', 'sale'])->nullable()->change();
            $table->decimal('commission_percentage', 5, 2)->nullable()->default(null)->change();
        });
    }
};
