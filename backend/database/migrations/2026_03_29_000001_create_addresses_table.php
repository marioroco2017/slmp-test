<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('street', 100)->nullable();   // e.g. "Kulas Light"
            $table->string('suite', 50)->nullable();    // e.g. "Apt. 556"
            $table->string('city', 100)->nullable();
            $table->string('zipcode', 20)->nullable();  // e.g. "92998-3874"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
