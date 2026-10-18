<?php

namespace App\Enums\Commons\LeaveRequests;

enum LeaveRequestsEnum: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return match ($value) {
            0 => self::PENDING,
            1 => self::APPROVED,
            2 => self::REJECTED,
            default => null,
        };
    }

    public function boolValue(): bool
    {
        return $this === self::APPROVED;
    }
};
