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
            'text' => ['required', 'string', 'max:250'],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['nullable', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => trans('tickets.validations.comment.text_required'),
            'text.string' => trans('tickets.validations.comment.text_string'),
            'text.max' => trans('tickets.validations.comment.text_max'),
        ];
    }
}
