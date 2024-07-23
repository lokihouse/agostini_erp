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
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->foreignIdFor(\App\Models\Cliente::class);
            $table->foreignIdFor(\App\Models\User::class)->nullable();
            $table->date('data');
            $table->enum('status', ['agendada', 'iniciada', 'finalizada', 'cancelada']);
            $table->string('motivo')->nullable();
            $table->text('observacao_cancelamento')->nullable();
            $table->dateTime('hora_inicial')->nullable();
            $table->text('observacao_inicial')->nullable();
            $table->string('imagem_inicial')->nullable();
            $table->dateTime('hora_final')->nullable();
            $table->text('observacao_final')->nullable();
            $table->boolean('tem_pedido')->nullable();
            $table->timestamps();
        });

        Schema::table('visitas', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Visita::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitas');
    }
};
