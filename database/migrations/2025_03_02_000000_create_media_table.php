<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')
                ->comment('The ID of the media from provider');
            $table->unsignedBigInteger('imdb_id');
            $table->enum('type', ['movie', 'tv-show']);
            $table->string('title');
            $table->string('poster')->nullable();
            $table->date('release_date')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Index for faster lookup
            $table->unique(['tmdb_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
