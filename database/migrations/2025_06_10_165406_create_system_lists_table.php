<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_lists', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('type');
            $table->string('poster');

            $table->json('items')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_lists');
    }
};
