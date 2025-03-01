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
        Schema::create('watchlist_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('watchlist_id')
                ->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('item_id');
            $table->string('type');
            $table->string('poster')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watchlist_items');
    }
};
