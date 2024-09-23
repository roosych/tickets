<?php

namespace App\Http\Requests\Tickets;

use App\Exceptions\TicketDepartmentAuthorizationException;
use App\Models\Ticket;
use App\Rules\TagBelongsToDepartment;
use App\Services\TicketService;
use Illuminate\Foundation\Http\FormRequest;

class AttachTagsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'tags' => ['array'],
            'tags.*' => ['required', 'exists:tags,id'],
        ];
    }
}
