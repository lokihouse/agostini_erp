<?php

use App\Models\Empresa;
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
            $table->foreignIdFor(Empresa::class);
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('valor_minimo', 10, 2)->nullable();
            $table->decimal('valor_venda', 10, 2)->nullable();
            $table->integer('tempo_de_producao')->default(0);
            $table->longText('mapa_de_producao')->nullable();
            $table->json('volumes')->nullable();
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
