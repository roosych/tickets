<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AttachUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'users' => ['array'],
            'users.*' => ['required', 'exists:users,id'],
            'ticket_id' => ['required', 'exists:tickets,id'],
        ];
    }
}
