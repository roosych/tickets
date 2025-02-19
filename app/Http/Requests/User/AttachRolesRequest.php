<?php

namespace App\Http\Requests\User;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class AttachRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'roles' => ['nullable', 'array', 'max:10'],
            'roles.*' => [
                'integer',
                'exists:roles,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $role = Role::find($value);

                    if (!$role || $role->department_id !== $user->getDepartmentId()) {
                        $fail('Выбранная роль не принадлежит вашему департаменту');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Выберите роль',
            'roles.max' => 'Слишком много ролей выбрано',
            'roles.*.exists' => 'Выбранная роль не существует',
        ];
    }
}
