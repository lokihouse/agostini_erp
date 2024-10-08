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
        Schema::create('produto_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Produto::class);
            // $table->integer('ordem');
            $table->foreignIdFor(\App\Models\Equipamento::class, 'equipamento_id_origem');
            $table->json('insumos')->nullable();
            $table->foreignIdFor(\App\Models\Equipamento::class, 'equipamento_id_destino');
            $table->json('producao')->nullable();
            $table->integer('tempo_producao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_etapas');
    }
};
