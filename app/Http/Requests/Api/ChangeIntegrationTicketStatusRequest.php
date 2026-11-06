<?php

namespace App\Http\Requests\Api;

use App\Enums\TicketStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeIntegrationTicketStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Внешняя система может отметить работу сделанной (done) или отменить тикет. Закрывает (completed) только менеджер.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in([TicketStatusEnum::DONE->value, TicketStatusEnum::CANCELED->value])],
            'comment' => ['nullable', 'string', 'max:2000'],
            'performed_by' => ['nullable', 'array'],
            'performed_by.guid' => ['nullable', 'string', 'max:100'],
            'performed_by.username' => ['nullable', 'string', 'max:100'],
            'performed_by.email' => ['nullable', 'string', 'max:255'],
        ];
    }
}
