<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic, Pro, Business
            $table->string('slug')->unique(); // basic, pro, business
            $table->boolean('is_free')->default(false);
            $table->decimal('price', 8, 2)->default(0);
            $table->json('features'); // Лимиты и возможности
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};