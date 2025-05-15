<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_visits', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('scheduled_by_user_id')->comment('Usuário que agendou')->constrained('users', 'uuid')->restrictOnDelete();
            $table->foreignUuid('assigned_to_user_id')->comment('Usuário que realizou/realizará')->constrained('users', 'uuid')->restrictOnDelete();

            $table->dateTime('scheduled_at')->comment('Data/Hora Agendada');
            $table->timestamp('visit_start_time')->nullable();
            $table->timestamp('visit_end_time')->nullable();
            $table->string('report_reason_no_order')->nullable();
            $table->text('report_corrective_actions')->nullable();
            $table->dateTime('visited_at')->nullable()->comment('Data/Hora da Realização');

            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('notes')->nullable()->comment('Observações gerais da visita');

            $table->string('cancellation_reason')->nullable();
            $table->text('cancellation_details')->nullable();

            $table->foreignUuid('sales_order_id')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'client_id']);
            $table->index(['company_id', 'assigned_to_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_visits');
    }
};
