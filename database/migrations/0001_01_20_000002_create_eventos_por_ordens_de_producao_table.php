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
        Schema::create('eventos_por_ordem_de_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\OrdemDeProducao::class);
            $table->foreignIdFor(\App\Models\User::class);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->boolean('soma_tempo_de_producao')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos_por_ordem_de_producao');
    }
};
