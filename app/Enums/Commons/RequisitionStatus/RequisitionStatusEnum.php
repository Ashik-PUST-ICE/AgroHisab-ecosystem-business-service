<?php

namespace App\Enums\Commons\RequisitionStatus;

enum RequisitionStatusEnum: int
{
    case PENDING = 0;
    case FIRST_APPROVED = 4;
    case APPROVED = 1;
    case PURCHASED = 2;
    case CANCELLED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::FIRST_APPROVED => 'First Approved',
            self::APPROVED => 'Approved',
            self::PURCHASED => 'Purchased',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    public function boolValue(): bool
    {
        return $this === self::APPROVED || $this === self::PURCHASED;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isFirstApproved(): bool
    {
        return $this === self::FIRST_APPROVED;
    }

    public function isPurchased(): bool
    {
        return $this === self::PURCHASED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isFinal(): bool
    {
        return $this === self::PURCHASED || $this === self::CANCELLED;
    }

    public function canBeModified(): bool
    {
        return ! $this->isFinal();
    }

    public static function options(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::FIRST_APPROVED->value => self::FIRST_APPROVED->label(),
            self::APPROVED->value => self::APPROVED->label(),
            self::PURCHASED->value => self::PURCHASED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
