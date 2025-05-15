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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('chart_of_account_uuid')->constrained('chart_of_accounts', 'uuid')->comment('Conta do plano de contas');
            $table->string('description')->nullable()->comment('Descrição da movimentação');
            $table->decimal('amount', 15, 2)->comment('Valor da movimentação');
            $table->enum('type', ['income', 'expense'])->comment('Tipo: entrada/receita, saida/despesa'); // ou 'credit', 'debit'
            $table->date('transaction_date')->comment('Data da movimentação');
            $table->foreignUuid('user_id')->nullable()->constrained('users', 'uuid')->comment('Usuário que registrou (opcional)');
            $table->string('payment_method')->nullable();
            $table->string('reference_document')->nullable(); // Nota fiscal, boleto, etc.
            $table->text('notes')->nullable()->comment('Observações adicionais');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
