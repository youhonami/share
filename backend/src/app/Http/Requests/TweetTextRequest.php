<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TweetTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'つぶやきを入力してください。',
            'text.max' => 'つぶやきは120文字以内で入力してください。',
        ];
    }
}
