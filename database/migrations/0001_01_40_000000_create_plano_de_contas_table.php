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
            $table->string('codigo')->unique();
            $table->string('nome', 100);
            $table->enum('tipo', ['ativo', 'passivo', 'receita', 'despesa', 'patrimonio_liquido']);
            $table->timestamps();
            $table->softDeletes();
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
