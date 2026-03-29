<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {


        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('url', 500);
            $table->string('thumbnail_url', 500);
            $table->timestamps();

            $table->index('album_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
