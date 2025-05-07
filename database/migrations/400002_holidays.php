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
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->string('type')->comment('Ex: national, state, municipal, optional_point'); // Feriado Nacional, Estadual, Municipal, Ponto Facultativo
            $table->boolean('is_recurrent')->default(true)->comment('Se o feriado se repete anualmente nesta data');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
