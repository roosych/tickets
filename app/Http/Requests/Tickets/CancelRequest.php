<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class CancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'exists:tickets,id'],
            'cancelled_comment' => ['required', 'string', 'max:10000']
        ];
    }

    public function messages(): array
    {
        return [
            'cancelled_comment.required' => trans('tickets.validations.comment.text_required'),
            'cancelled_comment.max' => trans('tickets.validations.comment.text_max'),
        ];
    }
}
