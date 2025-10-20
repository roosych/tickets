<?php

namespace App\Http\Requests\Tickets\Approval;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'approvers' => ['required', 'array', 'min:1'],
            'approvers.*' => ['integer', 'exists:users,id'],
            'approval_request_comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'approvers.required' => 'Вы должны выбрать хотя бы одного согласующего.',
            'approvers.array' => 'Согласующие должны быть массивом.',
            'approvers.min' => 'Выберите хотя бы одного согласующего.',
            'approvers.*.integer' => 'Некорректный идентификатор пользователя.',
            'approvers.*.exists' => 'Выбранный пользователь не найден.',
            'approval_request_comment.string' => 'Комментарий должен быть строкой.',
            'approval_request_comment.max' => 'Комментарий не может превышать 1000 символов.',
        ];
    }
}
