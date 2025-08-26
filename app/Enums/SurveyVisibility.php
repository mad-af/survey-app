<?php

namespace App\Enums;

enum SurveyVisibility: string
{
    case PRIVATE = 'private';
    case LINK = 'link';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match($this) {
            self::PRIVATE => 'Private',
            self::LINK => 'Link Only',
            self::PUBLIC => 'Public',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PRIVATE => 'Only invited respondents can access',
            self::LINK => 'Anyone with the link can access',
            self::PUBLIC => 'Publicly listed and searchable',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PRIVATE => 'lock',
            self::LINK => 'link',
            self::PUBLIC => 'globe',
        };
    }

    public function requiresInvitation(): bool
    {
        return $this === self::PRIVATE;
    }

    public function isPubliclyAccessible(): bool
    {
        return in_array($this, [self::LINK, self::PUBLIC]);
    }

    public static function options(): array
    {
        return [
            self::PRIVATE->value => self::PRIVATE->label(),
            self::LINK->value => self::LINK->label(),
            self::PUBLIC->value => self::PUBLIC->label(),
        ];
    }
}