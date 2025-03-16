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
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->foreignIdFor(\App\Models\PlanoDeConta::class);
            $table->string('descricao')->nullable();
            $table->decimal('valor', 15, 2);
            $table->enum('tipo', ['entrada', 'saida'])->default('entrada');
            $table->date('data_movimentacao');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
