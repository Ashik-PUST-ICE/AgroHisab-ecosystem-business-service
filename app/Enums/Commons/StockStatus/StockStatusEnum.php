<?php

namespace App\Enums\Commons\StockStatus;

enum StockStatusEnum: int
{
    case AVAILABLE = 1;
    case ASSIGNED = 2;
    case DEACTIVATED = 3;
    case DAMAGED = 4;
    case VENDOR_RETURN = 5;
    case EXPIRED = 6;
    case LOST = 7;
    case WARRANTY = 8;
    case TRANSFERRED = 9;
    case OTHER = 10;

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::ASSIGNED => 'Assigned',
            self::DEACTIVATED => 'Deactivated',
            self::DAMAGED => 'Damaged',
            self::VENDOR_RETURN => 'Vendor Return',
            self::EXPIRED => 'Expired',
            self::LOST => 'Lost',
            self::WARRANTY => 'Warranty',
            self::TRANSFERRED => 'Transferred',
            self::OTHER => 'Other',
        };
    }

    public static function fromValue(int $value): ?self
    {
        return self::tryFrom($value);
    }

    public function isAvailable(): bool
    {
        return $this === self::AVAILABLE;
    }

    public function isDeactivated(): bool
    {
        return $this === self::DEACTIVATED;
    }

    public function isVendorReturn(): bool
    {
        return $this === self::VENDOR_RETURN;
    }

    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }

    public function isLost(): bool
    {
        return $this === self::LOST;
    }

    public function isWarranty(): bool
    {
        return $this === self::WARRANTY;
    }

    public function isTransferred(): bool
    {
        return $this === self::TRANSFERRED;
    }

    public function isDamaged(): bool
    {
        return $this === self::DAMAGED;
    }

    public function isOther(): bool
    {
        return $this === self::OTHER;
    }

    public static function options(): array
    {
        return [
            self::AVAILABLE->value => self::AVAILABLE->label(),
            self::ASSIGNED->value => self::ASSIGNED->label(),
            self::DEACTIVATED->value => self::DEACTIVATED->label(),
            self::VENDOR_RETURN->value => self::VENDOR_RETURN->label(),
            self::DAMAGED->value => self::DAMAGED->label(),
            self::EXPIRED->value => self::EXPIRED->label(),
            self::LOST->value => self::LOST->label(),
            self::WARRANTY->value => self::WARRANTY->label(),
            self::TRANSFERRED->value => self::TRANSFERRED->label(),
            self::OTHER->value => self::OTHER->label(),
        ];
    }
}
