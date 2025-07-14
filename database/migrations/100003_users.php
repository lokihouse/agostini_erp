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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid()->primary();

            $table->foreignUuid('company_id')
                ->constrained(table: 'companies', column: 'uuid')
                ->cascadeOnDelete();

            $table->foreignUuid('work_shift_id')
                ->nullable()
                ->constrained('work_shifts', 'uuid')
                ->nullOnDelete();

            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                try {
                    $table->dropForeign(['company_id']);
                    $table->dropColumn('company_id');
                } catch (\Exception $e) {
                    if (Schema::hasColumn('users', 'company_id')) {
                        $table->dropColumn('company_id');
                    }
                }
            }
        });

        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
