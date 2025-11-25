<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => [
                'required', 
                'string', 
                'max:2000', 
                'min:1'
            ],
            'model' => [
                'sometimes', 
                'nullable', 
                Rule::exists('providers', 'key')
            ],
            'assistant_id' => [
                'required', 
                'integer', 
                // Добавить проверку существования ассистента
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Сообщение не может быть пустым',
            'message.max' => 'Сообщение слишком длинное (макс. 2000 символов)',
            'message.min' => 'Сообщение слишком короткое',
            'model.exists' => 'Указанная модель AI не существует',
            'assistant_id.required' => 'Необходимо указать ID ассистента'
        ];
    }
}