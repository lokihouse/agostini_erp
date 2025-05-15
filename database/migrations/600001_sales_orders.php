<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('client_id')->constrained('clients', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('sales_visit_id')->nullable()->constrained('sales_visits', 'uuid')->nullOnDelete();
            $table->foreignUuid('user_id')->comment('Usuário que criou o pedido')->constrained('users', 'uuid')->restrictOnDelete();

            $table->string('order_number')->comment('Ex: PV-AAAA-NNNN');
            $table->date('order_date');
            $table->date('delivery_deadline')->nullable();

            $table->enum('status', ['draft', 'pending', 'approved', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');
            $table->decimal('total_amount', 15, 2)->default(0); // Calculado a partir dos itens

            $table->text('notes')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->text('cancellation_details')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'order_number']);
            $table->index(['company_id', 'client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
