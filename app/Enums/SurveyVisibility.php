<?php

namespace App\Enums;

enum SurveyVisibility: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match($this) {
            self::PRIVATE => 'Private', 
            self::PUBLIC => 'Public',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PRIVATE => 'Only invited respondents can access',
            self::PUBLIC => 'Publicly listed and searchable',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PRIVATE => 'lock',
            self::PUBLIC => 'globe',
        };
    }

    public function requiresInvitation(): bool
    {
        return $this === self::PRIVATE;
    }

    public function isPubliclyAccessible(): bool
    {
        return in_array($this, [self::PUBLIC]);
    }

    public static function options(): array
    {
        return [
            self::PRIVATE->value => self::PRIVATE->label(),
            self::PUBLIC->value => self::PUBLIC->label(),
        ];
    }
}