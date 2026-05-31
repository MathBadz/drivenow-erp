<?php

namespace App\Enums;

enum VehicleCategory: string
{
    case Sedan = 'sedan';
    case Hatchback = 'hatchback';
    case Suv = 'suv';
    case Van = 'van';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Sedan => 'Sedan',
            self::Hatchback => 'Hatchback',
            self::Suv => 'SUV',
            self::Van => 'Van',
            self::Pickup => 'Pickup Truck',
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
