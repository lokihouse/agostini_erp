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
        Schema::create('production_order_item_steps', function (Blueprint $table) {
            $table->uuid('uuid')->primary();

            $table->foreignUuid('production_order_item_uuid')
                ->constrained('production_order_items', 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('production_step_uuid')
                ->nullable()
                ->constrained('production_steps', 'uuid')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_order_item_steps');
    }
};
