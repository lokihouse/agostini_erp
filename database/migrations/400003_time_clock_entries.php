<?php

use App\Models\TimeClockEntry;
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
        Schema::create('time_clock_entries', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->cascadeOnDelete();
            $table->foreignUuid('company_id')->constrained('companies', 'uuid')->cascadeOnDelete(); // Assumindo que a batida também é vinculada à empresa do usuário no momento

            $table->timestamp('recorded_at'); // Data e hora da batida
            $table->string('type')->comment('Ex: clock_in, clock_out, start_break, end_break'); // Tipo de batida
            $table->string('status')
                ->default(TimeClockEntry::STATUS_NORMAL)
                ->comment('Status da batida de ponto (normal, alert, justified, approved, accounted)');

            $table->decimal('latitude', 10, 7)->nullable(); // GPS Latitude
            $table->decimal('longitude', 10, 7)->nullable(); // GPS Longitude

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->text('notes')->nullable()->comment('Observações manuais, ex: esquecimento de batida');
            $table->foreignUuid('approved_by')->nullable()->constrained('users', 'uuid')->nullOnDelete()->comment('Quem aprovou uma entrada manual/correção');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps(); // created_at e updated_at para o registro em si
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_clock_entries');
    }
};
