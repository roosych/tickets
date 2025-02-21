<?php

namespace App\Http\Requests\Settings\Departments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:departments,name,' . $this->department->id],
            'manager_id' => ['nullable', 'exists:users,id'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название департамента обязательно',
            'name.string' => 'Название департамента должно быть строкой.',
            'name.max' => 'Название департамента не должно превышать 255 символов.',
            'name.unique' => 'Департамент с таким названием уже существует',

            'manager_id.required' => 'Необходимо выбрать менеджера департамента.',
            'manager_id.exists' => 'Выбранный менеджер не найден в системе.',

            'users.array' => 'Список сотрудников должен быть массивом.',
            'users.*.exists' => 'Один или несколько выбранных сотрудников не найдены в системе.',

            'active.required' => 'Необходимо указать статус департамента.',
            'active.boolean' => 'Поле "Активен" должно быть булевым значением.',
        ];
    }
}
