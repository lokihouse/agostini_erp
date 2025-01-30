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
        Schema::create('pedido_de_vendas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class);
            $table->foreignIdFor(\App\Models\Cliente::class);
            $table->foreignIdFor(\App\Models\Visita::class)->nullable();
            $table->enum('status', ['novo', 'fechado', 'entregue', 'cancelado'])->default('novo');
            $table->text('justificativa')->nullable();
            $table->json('produtos')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_de_vendas');
    }
};
