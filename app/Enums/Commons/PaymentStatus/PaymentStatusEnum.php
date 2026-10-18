<?php

namespace App\Enums\Commons\PaymentStatus;

enum PaymentStatusEnum: int
{
    case PAID = 1;
    case UNPAID = 0;
    case DUE = 2;

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'paid',
            self::UNPAID => 'unpaid',
            self::DUE => 'due',

        };
    }
}
