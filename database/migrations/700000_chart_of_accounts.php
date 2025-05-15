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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->string('code')->comment('Código da conta contábil');
            $table->string('name', 100)->comment('Nome da conta contábil');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])
                ->comment('Tipo da conta: ativo, passivo, patrimonio_liquido, receita, despesa');
            $table->foreignUuid('parent_uuid')->nullable()->comment('Conta pai para estrutura hierárquica');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code'], 'chart_of_accounts_company_code_unique');
            $table->foreign('parent_uuid')->references('uuid')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
