<?php

namespace App\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'hex_color'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Заполните название тега',
            'text.max' => 'Слишком длинное название',
        ];
    }

}
