<?php

use App\Models\Evento;
use App\Models\OrdemDeProducao;
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
        Schema::create('ordem_de_producao_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrdemDeProducao::class);
            $table->foreignIdFor(Evento::class);
            $table->dateTime('inicio');
            $table->dateTime('termino');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_de_producao_eventos');
    }
};
