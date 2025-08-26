<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::CLOSED => 'Closed',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::DRAFT => 'Survey is being prepared and not yet published',
            self::ACTIVE => 'Survey is live and accepting responses',
            self::CLOSED => 'Survey is closed and no longer accepting responses',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::ACTIVE => 'green',
            self::CLOSED => 'red',
        };
    }

    public function canAcceptResponses(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }

    public static function options(): array
    {
        return [
            self::DRAFT->value => self::DRAFT->label(),
            self::ACTIVE->value => self::ACTIVE->label(),
            self::CLOSED->value => self::CLOSED->label(),
        ];
    }
}