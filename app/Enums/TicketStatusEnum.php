<?php

namespace App\Enums;

enum TicketStatusEnum :string
{
    case OPENED = 'opened';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public function label(): string
    {
        //return __('ticket_statuses.' . $this->value);
        return $this->value;
    }

    public function color(): string
    {
        return match($this) {
            self::OPENED => 'warning',
            self::IN_PROGRESS => 'primary',
            self::COMPLETED => 'success',
            self::CLOSED => 'danger',
        };
    }

    public static function getAllValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
