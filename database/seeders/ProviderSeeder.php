<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProviderSeeder extends Seeder
{
    public function run()
    {
        DB::table('providers')->insert([
            [
                'key' => 'openai_default',
                'name' => 'OpenAI Default',
                'type' => 'openai',
                'status' => 'active',
                'meta' => json_encode([
                    'models' => ['gpt-3.5-turbo', 'gpt-4', 'gpt-4o']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'huggingface_free',
                'name' => 'HuggingFace Free',
                'type' => 'huggingface',
                'status' => 'active',
                'meta' => json_encode([
                    'models' => ['distilgpt2', 'gpt2']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}