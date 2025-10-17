<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();

            $table->foreignUuid('chart_of_account_id')
                ->nullable()
                ->constrained('chart_of_accounts', 'uuid')
                ->cascadeOnDelete();

            $table->string('month', 7)->comment('Ex: 2025-09');
            $table->decimal('amount', 15, 2)->default(0)->comment('Valor projetado ou realizado');

            $table->enum('category', ['projection', 'investment', 'goal'])
                ->default('projection')
                ->comment('Tipo de linha do fluxo de caixa');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_flows');
    }
};