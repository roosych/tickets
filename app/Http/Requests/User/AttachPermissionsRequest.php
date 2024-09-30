<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AttachPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }


    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'max:20'],
            'permissions.*' => ['required', 'integer', 'exists:permissions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'Выберите полномочия',
            'permissions.max' => 'Слишком много полномочий выбрано',
            'permissions.*.exists' => 'Выбранного полномочия не существует',
        ];
    }
}
