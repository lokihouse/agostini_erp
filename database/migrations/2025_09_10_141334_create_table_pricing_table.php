<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Criar tabela se não existir
        if (!Schema::hasTable('pricing_table')) {
            Schema::create('pricing_table', function (Blueprint $table) {
                $table->id();
                $table->uuid('company_id'); // vínculo com empresa
                $table->uuid('product_id'); // vínculo com produto

                $table->decimal('custo_materia_prima', 12, 2);
                $table->decimal('despesas', 8, 2);
                $table->decimal('imposto', 8, 2);
                $table->decimal('comissao', 8, 2);
                $table->decimal('frete', 8, 2);
                $table->decimal('prazo', 8, 2);
                $table->decimal('vpc', 8, 2);
                $table->decimal('inadimplencia', 8, 2);
                $table->decimal('assistencia', 8, 2);
                $table->decimal('lucro', 8, 2);

                $table->decimal('valorDespesas', 8, 2)->nullable();
                $table->decimal('valorImposto', 8, 2)->nullable();
                $table->decimal('valorComissao', 8, 2)->nullable();
                $table->decimal('valorFrete', 8, 2)->nullable();
                $table->decimal('valorPrazo', 8, 2)->nullable();
                $table->decimal('valorVPC', 8, 2)->nullable();
                $table->decimal('valorInadimplencia', 8, 2)->nullable();
                $table->decimal('valorAssistencia', 8, 2)->nullable();

                $table->decimal('custo_produto', 12, 2)->nullable();
                $table->decimal('indice_preco', 12, 2)->nullable();
                $table->decimal('preco_final', 12, 2)->nullable();
                $table->decimal('comercializacao', 12, 2)->nullable();
                $table->decimal('lucro_total', 12, 2)->nullable();

                $table->timestamps();
                $table->softDeletes();

                // 🔑 Foreign Keys
                $table->foreign('company_id')->references('uuid')->on('companies')->onDelete('cascade');
                $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
            });
        } else {
            // Se já existir, apenas adiciona company_id se não tiver
            Schema::table('pricing_table', function (Blueprint $table) {
                if (!Schema::hasColumn('pricing_table', 'company_id')) {
                    $table->uuid('company_id')->after('id');
                    $table->foreign('company_id')->references('uuid')->on('companies')->onDelete('cascade');
                }
                if (!Schema::hasColumn('pricing_table', 'product_id')) {
                    $table->uuid('product_id')->after('company_id');
                    $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('pricing_table', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['company_id', 'product_id']);
        });
    }
};
