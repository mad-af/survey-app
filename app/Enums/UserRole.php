<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SURVEYOR = 'surveyor';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::SURVEYOR => 'Surveyor',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ADMIN => 'Full access to all features and user management',
            self::SURVEYOR => 'Can create and manage surveys',
        };
    }

    public static function options(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::SURVEYOR->value => self::SURVEYOR->label(),
        ];
    }
}