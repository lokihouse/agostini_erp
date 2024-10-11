<?php

use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Equipamento;
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
        Schema::create('produto_etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Empresa::class);
            $table->foreignIdFor(Produto::class);
            $table->foreignIdFor(Departamento::class, 'departamento_origem_id');
            $table->foreignIdFor(Equipamento::class, 'equipamento_origem_id')->nullable();
            $table->foreignIdFor(Departamento::class, 'departamento_destino_id');
            $table->foreignIdFor(Equipamento::class, 'equipamento_destino_id')->nullable();
            $table->text('descricao')->nullable();
            $table->json('producao')->nullable();
            $table->integer('tempo')->default(0);
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
