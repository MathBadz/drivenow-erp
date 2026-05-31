<?php

namespace App\Enums;

enum RentalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that occupy a vehicle and therefore cause booking conflicts.
     *
     * @return array<int, string>
     */
    public static function blocking(): array
    {
        return [self::Approved->value, self::Active->value];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
