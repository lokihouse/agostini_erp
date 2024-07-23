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
        Schema::create('registros_de_ponto', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class);
            $table->date('dia');
            $table->time('hora');
            $table->ipAddress('ip');
            $table->float('latitude', 13, 10)->nullable();
            $table->float('longitude', 13, 10)->nullable();
            $table->string('status')->default('');
            $table->string('motivo_status')->default('');
            $table->text('justificativa')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_de_ponto');
    }
};
