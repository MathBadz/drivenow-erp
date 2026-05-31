<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Gcash = 'gcash';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Gcash => 'GCash',
            self::Card => 'Card',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $m) => $m->value, self::cases());
    }
}
