<?php

namespace App\Http\Requests\Roles;

use App\Rules\SameDepartmentRule;
use App\Rules\TicketBelongsToDepartment;
use Illuminate\Foundation\Http\FormRequest;

class AttachUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'users' => ['required', 'array'],
            'users.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
