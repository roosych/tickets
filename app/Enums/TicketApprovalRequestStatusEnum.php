<?php

namespace App\Enums;

enum TicketApprovalRequestStatusEnum: string
{
    case PENDING = 'pending';      // ожидает одобрения
    case APPROVED = 'approved';    // одобрен
    case DENIED = 'denied';    // отклонён
    case EXPIRED = 'expired';      // ссылка устарела

    public function is(self $status): bool
    {
        return $this === $status;
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Ожидается одобрение',
            self::APPROVED => 'Одобрен',
            self::DENIED => 'Отклонен',
            self::EXPIRED => 'Истёк срок',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::DENIED => 'danger',
            self::EXPIRED => 'secondary',
        };
    }

    public static function getAllValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
