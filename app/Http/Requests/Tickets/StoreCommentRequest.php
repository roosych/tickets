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
            'temp_folder' => ['nullable', 'string'],
            'text' => ['nullable', 'string', 'max:1000'],
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasText = $this->filled('text');
            $hasFiles = $this->hasTempFiles();

            if (!$hasText && !$hasFiles) {
                $validator->errors()->add('text', trans('tickets.validations.comment.comment_empty'));
            }
        });
    }

    protected function hasTempFiles(): bool
    {
        $folder = $this->input('temp_folder');

        if (!$folder) {
            return false;
        }

        $tempPath = storage_path('app/public/uploads/tmp/' . $folder);

        return \File::exists($tempPath) && count(\File::files($tempPath)) > 0;
    }

}
