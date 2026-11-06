<?php

namespace App\Http\Requests\Api;

use App\Services\Integration\IntegrationTicketService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIntegrationTicketRequest extends FormRequest
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
            'external_system' => ['required', 'string', 'max:50'],
            'external_id' => ['required', 'string', 'max:100'],
            'text' => ['required', 'string', 'max:20000'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'priority_id' => ['required', 'integer', 'exists:priorities,id'],
            'due_date' => ['nullable', 'date'],
            // Родителем может быть только тикет, созданный этим же техническим пользователем через API.
            'parent_id' => ['nullable', 'integer', Rule::exists('tickets', 'id')->where('user_id', $this->user()->id)->whereNotNull('external_system')],
            'parent_external_id' => ['nullable', 'string', 'max:100'],
            'executor' => ['nullable', 'array'],
            'executor.guid' => ['nullable', 'string', 'max:100'],
            'executor.username' => ['nullable', 'string', 'max:100'],
            'executor.email' => ['nullable', 'string', 'max:255'],
            'notify' => ['nullable', Rule::in([IntegrationTicketService::NOTIFY_ALL, IntegrationTicketService::NOTIFY_EXECUTOR, IntegrationTicketService::NOTIFY_NONE])],
        ];
    }
}
