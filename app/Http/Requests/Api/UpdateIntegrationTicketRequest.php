<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'due_date' => ['sometimes', 'nullable', 'date'],
            'executor' => ['sometimes', 'nullable', 'array'],
            'executor.guid' => ['nullable', 'string', 'max:100'],
            'executor.username' => ['nullable', 'string', 'max:100'],
            'executor.email' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
