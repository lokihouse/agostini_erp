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
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class);
            $table->foreignIdFor(\App\Models\Cliente::class);
            $table->foreignIdFor(\App\Models\PedidoDeVenda::class)->nullable();
            $table->date('data');
            $table->enum('status', ['agendada', 'em andamento', 'finalizada']);
            $table->json('relatorio_de_visita')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
