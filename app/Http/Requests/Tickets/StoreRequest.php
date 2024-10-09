<?php

namespace App\Http\Requests\Tickets;

use App\Models\Tag;
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
            'text' => ['required', 'string', 'max:10000'],
            'priority' => ['required', 'exists:priorities,id'],
            'department' => ['required', 'exists:departments,id'],
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

//        'files' => ['nullable', 'array'],
//        'files.*' => ['required', 'file', 'mimes:jpeg,png,pdf,doc,docx,xls,xlsx', 'max:4096'],
        ];

        if ($this->has('parent_id')) {
            $rules['parent_id'] = ['required', 'exists:tickets,id'];
            $rules['user'] = ['nullable', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'text.required' => 'Заполните описание Вашей проблемы',
            'text.max' => 'Описание проблемы не может содержать более 10000 символов',

            'priority.required' => 'Выберите приоритет',
            'priority.exists' => 'Приоритет не существует',

            'department.required' => 'Выберите отдел',
            'department.exists' => 'Отдел не существует',

            'files.*.mimes' => 'Допустимые форматы файлов: jpeg, png, pdf, doc, docx, xls, xlsx',
            'files.*.max' => 'Максимальный размер файла не должен превышать 4 МБ.',
        ];
    }
}
