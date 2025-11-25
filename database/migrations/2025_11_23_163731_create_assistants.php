<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Сначала создаем таблицу projects, если она еще не существует
        if (!Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->enum('visibility', ['private', 'shared', 'public'])->default('private');
                $table->json('settings')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('assistants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Изменим определение внешнего ключа project_id
            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained('projects')
                  ->nullOnDelete();
            
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Настройки AI
            $table->string('model')->default('gpt-3.5-turbo');
            $table->text('system_prompt')->nullable();
            $table->json('response_template')->nullable();
            
            // Лимиты и права
            $table->integer('max_tokens')->default(1000);
            $table->decimal('temperature', 3, 2)->default(0.7);
            $table->decimal('top_p', 3, 2)->default(1.0);
            
            // Дополнительные настройки
            $table->json('context_settings')->nullable();
            $table->json('additional_params')->nullable();
            
            // Статусы
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            
            $table->timestamps();
        });

        Schema::create('assistant_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('assistant_category_pivot', function (Blueprint $table) {
            $table->foreignId('assistant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('assistant_categories')->cascadeOnDelete();
            $table->primary(['assistant_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_category_pivot');
        Schema::dropIfExists('assistant_categories');
        Schema::dropIfExists('assistants');
    }
};