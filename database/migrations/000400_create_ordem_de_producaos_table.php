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
        Schema::create('ordens_de_producao', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->foreignIdFor(\App\Models\User::class, 'user_id');
            $table->enum('status', ['rascunho', 'agendada', 'em_producao', 'finalizada', 'cancelada'])->default('rascunho');
            $table->text('motivo_cancelamento')->nullable();
            $table->decimal('completude', 5, 2)->default(0);
            $table->date('previsao_inicio')->nullable();
            $table->date('previsao_final')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_final')->nullable();
            $table->json('produtos')->nullable();
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
