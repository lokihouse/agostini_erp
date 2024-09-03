<?php

use App\Models\Empresa;
use App\Models\OrdemDeProducao;
use App\Models\Produto;
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
        Schema::create('ordem_de_producao_produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrdemDeProducao::class);
            $table->foreignIdFor(Produto::class);
            $table->integer('quantidade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordem_de_producao_produtos');
    }
};
