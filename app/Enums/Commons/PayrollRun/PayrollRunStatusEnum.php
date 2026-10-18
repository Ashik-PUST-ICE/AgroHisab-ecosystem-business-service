<?php

namespace App\Enums\Commons\PayrollRun;

enum PayrollRunStatusEnum: int
{
    case DRAFT = 1;
    case CALCULATED = 2;
    case LOCKED = 3;
    case POSTED = 4;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::CALCULATED => 'Calculated',
            self::LOCKED => 'Locked',
            self::POSTED => 'Posted',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    public function boolValue(): bool
    {
        return $this === self::POSTED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isCalculated(): bool
    {
        return $this === self::CALCULATED;
    }

    public function isLocked(): bool
    {
        return $this === self::LOCKED;
    }

    public function isPosted(): bool
    {
        return $this === self::POSTED;
    }

    public function isFinal(): bool
    {
        return $this === self::POSTED;
    }

    public function canBeModified(): bool
    {
        return ! $this->isFinal();
    }

    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::CALCULATED->value => self::CALCULATED->label(),
            self::LOCKED->value => self::LOCKED->label(),
            self::POSTED->value => self::POSTED->label(),
        ];
    }
}
