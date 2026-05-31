<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Rented = 'rented';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Rented => 'Rented',
            self::Maintenance => 'Under Maintenance',
            self::Inactive => 'Inactive',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
