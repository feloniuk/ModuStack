<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // openai_default, huggingface_free
            $table->string('name');
            $table->enum('type', ['openai', 'huggingface', 'custom'])->default('openai');
            $table->enum('status', ['active', 'inactive', 'degraded'])->default('active');
            $table->json('meta')->nullable(); // Дополнительные настройки провайдера
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};