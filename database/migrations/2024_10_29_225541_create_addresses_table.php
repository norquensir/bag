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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('postal')->index()->nullable();
            $table->string('street_number')->index()->nullable();
            $table->string('street_number_ext')->index()->nullable();
            $table->string('street_number_add')->index()->nullable();
            $table->foreignId('place_id')->nullable()->constrained();
            $table->foreignId('public_space_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
