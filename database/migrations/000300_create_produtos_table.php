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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class)->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->longText('descricao')->nullable();
            $table->decimal('valor_unitario', 10, 2)->nullable();
            $table->longText('mapa_de_producao')->nullable();
            $table->integer('tempo_producao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
