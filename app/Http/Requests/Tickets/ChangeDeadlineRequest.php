<?php

namespace App\Http\Requests\Tickets;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class ChangeDeadlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'due_date' => ['required', 'date',
                function ($attribute, $value, $fail) {
                    $currentDeadline = $this->ticket->due_date ?? null; // текущий дедлайн
                    $newDeadline = Carbon::parse($value);

                    if ($currentDeadline && $newDeadline->lt(Carbon::parse($currentDeadline))) {
                        $fail(trans('tickets.validations.deadline.after_or_equal'));
                    }
                }
            ],
            'deadline_comment' => ['required', 'string', 'min:2', 'max:1000']
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.required' => trans('tickets.validations.deadline.required'),
            'due_date.date' => trans('tickets.validations.deadline.date'),

            'deadline_comment.required' => trans('tickets.validations.comment.text_required'),
            'deadline_comment.string' => trans('tickets.validations.comment.text_string'),
            'deadline_comment.min' => trans('tickets.validations.comment.text_min'),
            'deadline_comment.max' => trans('tickets.validations.comment.text_max'),
        ];
    }
}
