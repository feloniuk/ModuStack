<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('model_name');
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->integer('cost_cents')->nullable(); // Для будущего биллинга
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->json('meta')->nullable(); // Дополнительные метаданные
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};