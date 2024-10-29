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
        Schema::create('trailer_spots', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->json('polygons')->nullable();
            $table->foreignId('address_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trailer_spots');
    }
};
