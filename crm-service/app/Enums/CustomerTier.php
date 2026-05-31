<?php

namespace App\Enums;

enum CustomerTier: string
{
    case Regular = 'regular';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Derive a loyalty tier from accumulated points.
     */
    public static function fromPoints(int $points): self
    {
        return match (true) {
            $points >= 5000 => self::Platinum,
            $points >= 2000 => self::Gold,
            $points >= 500 => self::Silver,
            default => self::Regular,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }
}
