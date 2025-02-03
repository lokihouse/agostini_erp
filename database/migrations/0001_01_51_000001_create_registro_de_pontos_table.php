<?php

use App\Models\User;
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
        Schema::create('registro_de_pontos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->dateTime('data');
            $table->string('ip');
            $table->string('device_id');
            $table->float('latitude');
            $table->float('longitude');
            $table->float('accuracy');
            $table->string('address');
            $table->string('hash');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_de_pontos');
    }
};
