<?php

namespace App\Enums;

enum ResponseStatus: string
{
    case STARTED = 'started';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case ABANDONED = 'abandoned';

    public function label(): string
    {
        return match($this) {
            self::STARTED => 'Started',
            self::IN_PROGRESS => 'In Progress',
            self::COMPLETED => 'Completed',
            self::ABANDONED => 'Abandoned',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::STARTED => 'Response has been initiated',
            self::IN_PROGRESS => 'Response is being filled out',
            self::COMPLETED => 'Response has been submitted',
            self::ABANDONED => 'Response was started but not completed',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::STARTED => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::COMPLETED => 'green',
            self::ABANDONED => 'red',
        };
    }

    public function isComplete(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeModified(): bool
    {
        return in_array($this, [self::STARTED, self::IN_PROGRESS]);
    }

    public static function options(): array
    {
        return [
            self::STARTED->value => self::STARTED->label(),
            self::IN_PROGRESS->value => self::IN_PROGRESS->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::ABANDONED->value => self::ABANDONED->label(),
        ];
    }
}