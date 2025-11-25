<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->string('resource_type'); // ai_request, assistant, etc.
            $table->integer('tokens_used')->default(0);
            $table->integer('requests_count')->default(1);
            $table->json('metadata')->nullable();
            $table->date('log_date');
            $table->timestamps();

            $table->unique(['user_id', 'log_date', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};