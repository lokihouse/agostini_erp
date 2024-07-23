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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('cnpj', 14);
            $table->string('razao_social');
            $table->string('nome_fantasia');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('municipio');
            $table->string('uf');
            $table->string('cep');
            $table->string('email')->nullable();
            $table->string('telefone')->nullable();
            $table->float('latitude');
            $table->float('longitude');
            $table->integer('raio_cerca')->nullable();
            $table->json('horarios')->nullable();
            $table->integer('tolerancia_turno')->nullable();
            $table->integer('tolerancia_jornada')->nullable();
            $table->integer('justificativa_dias')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
