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
        Schema::create('production_order_logs', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_order_uuid')
                ->constrained('production_orders', 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_order_item_uuid')
                ->constrained('production_order_items', 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_step_uuid')
                ->nullable()
                ->constrained('production_steps', 'uuid')
                ->restrictOnDelete();

            $table->foreignUuid('user_uuid')
                ->constrained('users', 'uuid')
                ->restrictOnDelete();

            // Dados do registro
            $table->decimal('quantity', 15, 4)->nullable();
            $table->integer('ellapsed_time')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_logs');
    }
};
