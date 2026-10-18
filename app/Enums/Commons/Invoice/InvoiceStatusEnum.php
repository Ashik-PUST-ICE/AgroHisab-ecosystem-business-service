<?php

namespace App\Enums\Commons\Invoice;

enum InvoiceStatusEnum: int
{
    case UNPAID = 0;
    case PAID = 1;
    case PARTIALLY_PAID = 2;

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Paid',
            self::UNPAID => 'Unpaid',
            self::PARTIALLY_PAID => 'Partially Paid',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return match ($value) {
            0 => self::UNPAID,
            1 => self::PAID,
            2=> self::PARTIALLY_PAID,
            default => null,
        };
    }

    public function boolValue(): bool
    {
        return $this === self::PAID;
    }
}
