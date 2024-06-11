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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class)->constrained()->cascadeOnDelete();
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
            $table->integer('recorrencia_de_visitas_dias')->default(30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
