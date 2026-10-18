<?php

namespace App\Enums\Commons\EntityType;

enum EntityTypeEnum: string
{
    case CLIENT = 'client';
    case EMPLOYEE = 'employee';
    case BRANCH = 'branch';
    case VLAN = 'vlan';

    public function label(): string
    {
        return match ($this) {
            self::CLIENT => 'Client',
            self::EMPLOYEE => 'Employee',
            self::BRANCH => 'Branch',
            self::VLAN => 'VLAN',
        };
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public function isClient(): bool
    {
        return $this === self::CLIENT;
    }

    public function isEmployee(): bool
    {
        return $this === self::EMPLOYEE;
    }

    public function isBranch(): bool
    {
        return $this === self::BRANCH;
    }

    public function isVlan(): bool
    {
        return $this === self::VLAN;
    }

    public static function options(): array
    {
        return [
            self::CLIENT->value => self::CLIENT->label(),
            self::EMPLOYEE->value => self::EMPLOYEE->label(),
            self::BRANCH->value => self::BRANCH->label(),
            self::VLAN->value => self::VLAN->label(),
        ];
    }
}
