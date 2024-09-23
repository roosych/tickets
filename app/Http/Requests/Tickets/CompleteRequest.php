<?php

namespace App\Http\Requests\Tickets;

use App\Exceptions\TicketDepartmentAuthorizationException;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class CompleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticketId = $this->input('ticket_id');
        if (!auth()->check() || !$ticketId) {
            return false; // Если нет ID тикета, отклоняем запрос
        }

        $ticket = Ticket::findOrFail($ticketId);
        if (!$this->ticketService->isTicketInUserDepartment($this->user(), $ticket)) {
            throw new TicketDepartmentAuthorizationException();
        }

        return true;
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
            'completed_comment.required' => 'Пожалуйста, введите комментарий',
            'completed_comment.max' => 'Слишком длинный комментарий',
        ];
    }
}
