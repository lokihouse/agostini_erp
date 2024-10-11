<?php

use App\Models\Empresa;
use App\Models\User;
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
        Schema::create('ordens_de_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Empresa::class);
            $table->enum('status', ['rascunho', 'agendada', 'em_producao', 'finalizada', 'cancelada'])->default('rascunho');
            $table->date('data_inicio_agendamento')->nullable();
            $table->date('data_final_agendamento')->nullable();
            $table->date('data_inicio_producao')->nullable();
            $table->date('data_final_producao')->nullable();
            $table->date('data_finalizacao')->nullable();
            $table->date('data_cancelamento')->nullable();
            $table->longText('motivo_cancelamento')->nullable();
            $table->longText('mapa_de_processo')->nullable();
            $table->json('produtos')->nullable();
            $table->json('eventos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordens_de_producao');
    }
};
