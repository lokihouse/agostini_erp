<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pause_reasons', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies', 'uuid')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['productive_time', 'dead_time', 'mandatory_break'])->comment('Tipo de pausa: Tempo Produtivo, Tempo Morto, Pausa Obrigatória');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pause_reasons');
    }
};
