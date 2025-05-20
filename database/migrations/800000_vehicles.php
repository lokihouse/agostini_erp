<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            // Corrected line: explicitly reference the 'uuid' column in the 'companies' table
            $table->foreignUuid('company_id')->constrained(table: 'companies', column: 'uuid')->cascadeOnDelete();

            $table->string('license_plate')->comment('Placa do Veículo');
            $table->string('description')->nullable()->comment('Descrição Ex: Caminhão HR, Moto Entrega');
            $table->string('brand')->nullable()->comment('Marca Ex: Mercedes, Honda');
            $table->string('model_name')->nullable()->comment('Modelo Ex: Sprinter, CG 160');
            $table->year('year_manufacture')->nullable()->comment('Ano de Fabricação');
            $table->year('year_model')->nullable()->comment('Ano do Modelo');
            $table->string('color')->nullable()->comment('Cor');
            $table->decimal('cargo_volume_m3', 8, 3)->nullable()->comment('Volume de Carga em m³');
            $table->decimal('max_load_kg', 10, 2)->nullable()->comment('Carga Máxima em KG');
            $table->string('renavam')->nullable()->comment('RENAVAM');
            $table->boolean('is_active')->default(true)->comment('Se o veículo está ativo para uso');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'license_plate'], 'vehicle_company_plate_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
