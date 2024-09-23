<?php

namespace App\Http\Requests\Roles;

use App\Rules\SameDepartmentRule;
use Illuminate\Foundation\Http\FormRequest;

class DetachUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id', 'exists:users,id']
        ];
    }
}
