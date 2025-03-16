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
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->integer('tempo_de_producao_segundos')->default(0);
            $table->json('insumos')->nullable();
            $table->json('producao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('produto_etapas_origens', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\ProdutoEtapa::class);
            $table->foreignIdFor(\App\Models\ProdutoEtapa::class, 'produto_etapa_id_origem')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('produto_etapas_destinos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\ProdutoEtapa::class);
            $table->foreignIdFor(\App\Models\ProdutoEtapa::class, 'produto_etapa_id_destino')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produto_etapas');
        Schema::dropIfExists('produto_etapas_origens');
        Schema::dropIfExists('produto_etapas_destinos');
    }
};
