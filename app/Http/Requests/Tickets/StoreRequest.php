<?php

namespace App\Http\Requests\Tickets;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $rules = [
            'temp_folder' => ['nullable', 'string'],
            'text' => ['required', 'string', 'max:10000'],
            'priority' => ['required', 'exists:priorities,id'],
            'department' => ['required', 'exists:departments,id'],
            'user' => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $authUser = auth()->user();
                    $selectedUser = User::find($value);

                    if (!$selectedUser || $selectedUser->getDepartmentId() !== $authUser->getDepartmentId()) {
                        $fail('Выбранный пользователь не принадлежит вашему отделу.');
                    }
                },
            ],
            'tags' => ['array'],
            'tags.*' => [
                'required',
                'integer',
                'exists:tags,id',
                function ($attribute, $value, $fail) {
                    $user = auth()->user();
                    $tag = Tag::find($value);

                    if (!$tag || $tag->department_id !== $user->getDepartmentId()) {
                        $fail('Выбранный тег не принадлежит вашему департаменту');
                    }
                },
            ],
        ];

        if ($this->has('parent_id')) {
            $rules['parent_id'] = ['required', 'exists:tickets,id'];
        }
        if ($this->has('client')) {
            $rules['client'] = ['nullable', 'exists:users,id'];
        }
        if ($this->user()->isManager() && $this->has('is_private')) {
            $rules['is_private'] = ['boolean'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'text.required' => trans('tickets.validations.text_required'),
            'text.max' => trans('tickets.validations.text_max'),

            'priority.required' => trans('tickets.validations.priority_required'),
            'priority.exists' => trans('tickets.validations.priority_exists'),

            'department.required' => trans('tickets.validations.department_required'),
            'department.exists' => trans('tickets.validations.department_exists'),

            'files.*.mimes' => trans('tickets.validations.files_mimes'),
            'files.*.max' => trans('tickets.validations.files_max'),
        ];
    }
}
