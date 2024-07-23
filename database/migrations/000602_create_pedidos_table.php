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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->foreignIdFor(\App\Models\Visita::class);
            $table->enum('status', ['pendente', 'confirmado', 'em producao', 'finalizado', 'cancelado']);
            $table->text('observacao_cancelamento')->nullable();
            $table->dateTime('confirmacao')->nullable();
            $table->dateTime('producao')->nullable();
            $table->dateTime('entregaentrega')->nullable();
            $table->text('observacao_entrega')->nullable();
            $table->json('itens_de_pedido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
