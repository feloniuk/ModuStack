<?php

return [
    'default' => env('AI_DEFAULT', 'openai'),
    
    'providers' => [
        'openai' => [
            'enabled' => true,
            'key' => env('OPENAI_KEY'),
            'rate_limit' => env('OPENAI_RATE_LIMIT', 100)
        ],
        'huggingface' => [
            'enabled' => true,
            'mode' => 'free', // 'free' | 'inference' (paid)
            'key' => env('HUGGINGFACE_KEY'), 
            'rate_limit' => env('HF_RATE_LIMIT', 10)
        ],
    ],

    'fallback_order' => ['openai', 'huggingface']
];