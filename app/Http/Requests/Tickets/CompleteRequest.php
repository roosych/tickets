<?php

namespace App\Http\Requests\Tickets;

use App\Exceptions\TicketDepartmentAuthorizationException;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class CompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'exists:tickets,id'],
            'completed_comment' => ['required', 'string', 'max:10000']
        ];
    }

    public function messages(): array
    {
        return [
            'completed_comment.required' => trans('tickets.validations.comment.text_required'),
            'completed_comment.max' => trans('tickets.validations.comment.text_max'),
        ];
    }
}
