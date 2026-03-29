<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->unique()->constrained()->cascadeOnDelete();
            // DECIMAL(10,7): covers ±180.xxx, 7 decimal places ≈ 1cm GPS precision
            // API sends strings ("-37.3159") — cast to decimal on insert
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geos');
    }
};
