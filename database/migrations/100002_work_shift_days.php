<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_shift_days', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('work_shift_uuid')->constrained('work_shifts', 'uuid')->cascadeOnDelete();

            // 1=Segunda, 2=Terça, ..., 7=Domingo (ISO 8601 week day)
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_off_day')->default(false);

            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->time('interval_starts_at')->nullable();
            $table->time('interval_ends_at')->nullable();

            $table->timestamps();

            $table->unique(['work_shift_uuid', 'day_of_week']); // Garante um dia por jornada
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_shift_days');
    }
};
