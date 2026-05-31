<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Staff = 'staff';
    case Maintenance = 'maintenance';
    case Customer = 'customer';

    /**
     * Human-readable label for display in the UI.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Staff => 'Staff',
            self::Maintenance => 'Maintenance Staff',
            self::Customer => 'Customer',
        };
    }

    /**
     * Roles that may access the Operations Hub (admin back office).
     *
     * @return array<int, self>
     */
    public static function staffRoles(): array
    {
        return [self::Admin, self::Staff, self::Maintenance];
    }
}
