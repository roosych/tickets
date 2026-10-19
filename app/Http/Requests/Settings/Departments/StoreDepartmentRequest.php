<?php

namespace App\Http\Requests\Settings\Departments;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    protected $errorBag = 'createDepartment';

    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:departments,name'],
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
            'active.required' => 'Необходимо указать, исполняет ли департамент тикеты.',
            'active.boolean' => 'Некорректное значение поля.',
        ];
    }
}
