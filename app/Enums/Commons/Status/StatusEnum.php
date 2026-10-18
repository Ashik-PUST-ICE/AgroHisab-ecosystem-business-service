<?php

namespace App\Enums\Commons\Status;

enum StatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return match ($value) {
            0 => self::INACTIVE,
            1 => self::ACTIVE,
            default => null,
        };
    }

    public function boolValue(): bool
    {
        return $this === self::ACTIVE;
    }
}
