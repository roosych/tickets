<?php

namespace App\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'text' => [
                'required', 'string', 'max:100',
                Rule::unique('tags', 'text')->where('department_id', auth()->user()->getDepartmentId()),
            ],
            'color' => ['required', 'string', 'hex_color'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Заполните название тега',
            'text.max' => 'Слишком длинное название',
            'text.unique' => 'Тег с таким названием уже существует',
        ];
    }

}
