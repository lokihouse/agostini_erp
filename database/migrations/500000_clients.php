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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('uuid')->primary(); // Chave primária UUID

            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid')
                ->cascadeOnDelete(); // Exclui clientes se a empresa for excluída

            $table->string('name');
            $table->string('social_name');
            $table->string('taxNumber');
            $table->string('state_registration')->nullable();
            $table->string('municipal_registration')->nullable();

            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();

            $table->string('address_zip_code')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_complement')->nullable();
            $table->string('address_district')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state', 2)->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('status')->default('active');
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
