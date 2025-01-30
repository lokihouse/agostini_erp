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
        Schema::create('horarios_de_trabalho', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\JornadaDeTrabalho::class);
            $table->integer('dia_do_ciclo');
            $table->time('entrada')->nullable();
            $table->time('saida')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_de_trabalho');
    }
};
