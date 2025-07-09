<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            // Corrected: Explicitly reference 'uuid' column in 'companies'
            $table->foreignUuid('company_id')->constrained(table: 'companies', column: 'uuid')->cascadeOnDelete();
            $table->string('transport_order_number')->comment('Número da Ordem de Transporte');

            // Corrected: Explicitly reference 'uuid' column in 'vehicles'
            $table->foreignUuid('vehicle_id')->nullable()->constrained(table: 'vehicles', column: 'uuid')->nullOnDelete();
            // Corrected: Explicitly reference 'uuid' column in 'users'
            $table->foreignUuid('driver_id')->nullable()->constrained(table: 'users', column: 'uuid')->comment('Motorista')->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])->default('pending');

            $table->dateTime('planned_departure_datetime')->nullable()->comment('Data/Hora Prevista para Saída');
            $table->dateTime('actual_departure_datetime')->nullable()->comment('Data/Hora Efetiva da Saída');
            $table->dateTime('planned_arrival_datetime')->nullable()->comment('Data/Hora Prevista para Chegada (última entrega)');
            $table->dateTime('actual_arrival_datetime')->nullable()->comment('Data/Hora Efetiva da Chegada (última entrega)');

            $table->text('cancellation_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            // Corrected: Explicitly reference 'uuid' column in 'users'
            $table->foreignUuid('cancelled_by_user_id')->nullable()->constrained(table: 'users', column: 'uuid')->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // $table->unique(['company_id', 'transport_order_number'], 'transport_order_company_number_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_orders');
    }
};
