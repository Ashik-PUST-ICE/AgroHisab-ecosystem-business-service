<?php

namespace App\Enums\Commons\Payslip;

enum PayslipStatusEnum: int
{
    case CALCULATED = 1;
    case APPROVED = 2;
    case PAID = 3;
    case ADJUSTED = 4;

    public function label(): string
    {
        return match ($this) {
            self::CALCULATED => 'Calculated',
            self::APPROVED => 'Approved',
            self::PAID => 'Paid',
            self::ADJUSTED => 'Adjusted',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    public function isCalculated(): bool
    {
        return $this === self::CALCULATED;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    public function isAdjusted(): bool
    {
        return $this === self::ADJUSTED;
    }

    public function canBeModified(): bool
    {
        return ! $this->isPaid();
    }

    public static function options(): array
    {
        return [
            self::CALCULATED->value => self::CALCULATED->label(),
            self::APPROVED->value => self::APPROVED->label(),
            self::PAID->value => self::PAID->label(),
            self::ADJUSTED->value => self::ADJUSTED->label(),
        ];
    }

    public function nextStatus(): self
    {
        return match ($this) {
            self::CALCULATED => self::APPROVED,
            self::APPROVED => self::PAID,
            self::PAID => self::ADJUSTED,
            self::ADJUSTED => self::CALCULATED,
        };
    }
}
