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
        Schema::create('ordens_de_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->foreignIdFor(\App\Models\Cliente::class);
            $table->enum('status', ['novo', 'em fila', 'producao', 'finalizado', 'cancelado'])->default('novo');
            $table->date('data_programacao')->nullable();
            $table->date('data_producao')->nullable();
            $table->date('data_finalizacao')->nullable();
            $table->date('data_cancelamento')->nullable();
            $table->text('justificativa')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_de_producao');
    }
};
