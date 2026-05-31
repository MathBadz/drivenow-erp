<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case Inspection = 'inspection';
    case Repair = 'repair';
    case Scheduled = 'scheduled';
    case Damage = 'damage';

    public function label(): string
    {
        return match ($this) {
            self::Inspection => 'Inspection',
            self::Repair => 'Repair',
            self::Scheduled => 'Scheduled Service',
            self::Damage => 'Damage Report',
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
