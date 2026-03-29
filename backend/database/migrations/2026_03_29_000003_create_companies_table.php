<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name', 100);                          // e.g. "Romaguera-Crona"
            $table->string('catch_phrase', 255)->nullable();      // e.g. "Multi-layered client-server neural-net"
            $table->string('bs', 255)->nullable();                // e.g. "harness real-time e-markets"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
