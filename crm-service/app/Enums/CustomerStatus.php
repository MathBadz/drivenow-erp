<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Blacklisted = 'blacklisted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Blacklisted => 'Blacklisted',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
