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
        Schema::create('address_names', function (Blueprint $table) {
            $table->id();
            $table->string('name')->fulltext()->nullable();
            $table->string('full_street')->fulltext()->nullable();
            $table->string('full_address')->fulltext()->nullable();
            $table->foreignId('address_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_names');
    }
};
