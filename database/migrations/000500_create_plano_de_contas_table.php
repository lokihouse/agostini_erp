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
        Schema::create('plano_de_contas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Empresa::class);
            $table->string('codigo')->nullable();
            $table->string('descricao')->nullable();
            $table->boolean('status')->nullable();
            $table->boolean('movimentacao')->default(false);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->decimal('valor_projetado', 15,2)->nullable();
            $table->decimal('valor_realizado', 15,2)->nullable();
            $table->timestamps();
        });

        Schema::table('plano_de_contas', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\PlanoDeConta::class)->nullable()->after('empresa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_de_contas');
    }
};
