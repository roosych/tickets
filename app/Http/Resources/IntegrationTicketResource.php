<?php

namespace App\Http\Resources;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Тикет в ответе API для внешних систем.
 *
 * @mixin Ticket
 */
class IntegrationTicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => route('cabinet.tickets.show', $this->id),
            'status' => $this->status->value,
            'parent_id' => $this->parent_id,
            'executor' => $this->performer ? [
                'id' => $this->performer->id,
                'name' => $this->performer->name,
                'guid' => $this->performer->guid,
                'username' => $this->performer->username,
                'email' => $this->performer->email,
            ] : null,
            'due_date' => $this->due_date?->toIso8601String(),
            'external_system' => $this->external_system,
            'external_id' => $this->external_id,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
