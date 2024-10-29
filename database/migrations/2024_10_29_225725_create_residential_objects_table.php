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
        Schema::create('residential_objects', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->foreignId('address_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residential_objects');
    }
};
