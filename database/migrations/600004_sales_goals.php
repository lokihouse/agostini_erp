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
        Schema::create('sales_goals', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->cascadeOnDelete()->comment('Vendedor');
            $table->date('period')->comment('Primeiro dia do mês da meta (YYYY-MM-01)');
            $table->decimal('goal_amount', 15, 2)->comment('Valor da meta de vendas');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'user_id', 'period'], 'sales_goals_user_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_goals');
    }
};

