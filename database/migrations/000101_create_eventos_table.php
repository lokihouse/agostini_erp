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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class)->nullable();
            $table->string('nome');
            $table->longText('descricao')->nullable();
            $table->enum('tipo', ['producao', 'intervalo', 'tempo morto'])->nullable();
            $table->enum('credito_debito', ['credito', 'debito', 'nulo'])->default('nulo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
