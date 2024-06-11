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
            $table->foreignIdFor(\App\Models\Empresa::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Cliente::class)->constrained()->cascadeOnDelete();
            $table->dateTime('data');
            $table->enum('status', ['agendada', 'realizada', 'cancelada']);
            $table->timestamps();
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
