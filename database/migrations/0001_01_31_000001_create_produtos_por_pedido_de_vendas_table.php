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
        Schema::create('produtos_por_pedido_de_vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PedidoDeVenda::class);
            $table->foreignIdFor(\App\Models\Produto::class);
            $table->integer('quantidade');
            $table->float('desconto');
            $table->float('valor_original');
            $table->float('valor_final');
            $table->float('subtotal');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos_por_pedido_de_vendas');
    }
};
