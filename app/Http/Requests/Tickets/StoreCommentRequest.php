<?php

namespace App\Http\Requests\Tickets;

use App\Exceptions\TicketDepartmentAuthorizationException;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:10000'],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Введите комментарий',
            'text.string' => 'Введите текст',
            'text.max' => 'Слишком длинный комментарий',
        ];
    }
}
