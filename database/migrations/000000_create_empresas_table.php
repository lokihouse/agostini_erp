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
            $table->timestamps();

            $table->boolean('active')->default(false);

            $table->string('cnpj');
            $table->string('razao_social');
            $table->string('nome_fantasia');

            $table->string('cep');
            $table->string('logradouro');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('municipio');
            $table->string('uf');

            $table->string('email')->nullable();
            $table->string('telefone')->nullable();

            // Producao

            // Vendas

            // Financeiro

            // Recursos Humanos
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->integer('raio_cerca')->nullable();

            // Cargas
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
