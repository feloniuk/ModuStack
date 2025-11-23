<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    public function run()
    {
        DB::table('plans')->insert([
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'is_free' => true,
                'price' => 0,
                'features' => json_encode([
                    'requests_per_day' => 50,
                    'max_assistants' => 1,
                    'ai_models' => ['huggingface_free'],
                    'analytics' => 'basic'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'is_free' => false,
                'price' => 29.99,
                'features' => json_encode([
                    'requests_per_day' => 1000,
                    'max_assistants' => 5,
                    'ai_models' => ['openai', 'huggingface_free'],
                    'analytics' => 'advanced'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'is_free' => false,
                'price' => 99.99,
                'features' => json_encode([
                    'requests_per_day' => 10000,
                    'max_assistants' => null,
                    'ai_models' => ['openai', 'huggingface_free', 'custom'],
                    'analytics' => 'full',
                    'priority_support' => true
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}