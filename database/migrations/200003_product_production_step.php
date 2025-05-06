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
        Schema::create('product_production_step', function (Blueprint $table) {
            // Chave primária composta ou ID simples (opcional, mas útil)
            $table->id(); // Ou $table->uuid('uuid')->primary(); se preferir

            // Chaves estrangeiras referenciando as tabelas principais
            $table->foreignUuid('product_uuid')->constrained('products', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('production_step_uuid')->constrained('production_steps', 'uuid')->cascadeOnDelete();

            // Campos extras da relação (pivot)
            $table->integer('step_order')->default(0); // Ordem da etapa para este produto

            $table->timestamps(); // Opcional, mas útil para saber quando a relação foi criada/atualizada

            // Garantir que a combinação produto/etapa seja única (opcional, mas recomendado)
            $table->unique(['product_uuid', 'production_step_uuid'], 'product_step_unique');
            // Garantir que a ordem da etapa seja única para um produto (opcional, mas recomendado)
            $table->unique(['product_uuid', 'step_order'], 'product_step_order_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_production_step');
    }
};

