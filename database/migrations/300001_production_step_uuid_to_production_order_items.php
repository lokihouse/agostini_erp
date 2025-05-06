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
        Schema::table('production_order_items', function (Blueprint $table) {
            // Add after an existing column like 'product_uuid'
            $table->foreignUuid('production_step_uuid')
                ->nullable() // Or not nullable if every item MUST have a step
                ->after('product_uuid')
                ->constrained('production_steps', 'uuid')
                ->nullOnDelete(); // Or cascadeOnDelete() depending on your needs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_order_items', function (Blueprint $table) {
            // Add after an existing column like 'product_uuid'
            $table->foreignUuid('production_step_uuid')
                ->nullable() // Or not nullable if every item MUST have a step
                ->after('product_uuid')
                ->constrained('production_steps', 'uuid')
                ->nullOnDelete(); // Or cascadeOnDelete() depending on your needs
        });
    }
};
