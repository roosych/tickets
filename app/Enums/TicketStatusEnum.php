<?php

namespace App\Enums;

enum TicketStatusEnum :string
{
    case OPENED = 'opened';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public function label(): string
    {
        return trans('tickets.statuses.'.$this->value);
        //return $this->value;
    }

    public function color(): string
    {
        return match($this) {
            self::OPENED => 'dark',
            self::IN_PROGRESS => 'warning',
            self::DONE => 'primary',
            self::COMPLETED => 'success',
            self::CANCELED => 'danger',
        };
    }

    public static function getAllValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
