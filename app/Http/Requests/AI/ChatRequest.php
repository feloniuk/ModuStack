<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Здесь можно добавить дополнительные проверки
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'model' => ['sometimes', 'string', 'exists:providers,key']
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Сообщение не может быть пустым',
            'message.max' => 'Сообщение слишком длинное',
            'model.exists' => 'Указанная модель не существует'
        ];
    }
}