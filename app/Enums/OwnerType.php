<?php

namespace App\Enums;

enum OwnerType: string
{
    case SURVEY = 'survey';
    case SURVEY_SECTION = 'survey_section';

    public function label(): string
    {
        return match($this) {
            self::SURVEY => 'Survey',
            self::SURVEY_SECTION => 'Survey Section',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return collect(self::cases())->map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label()
        ])->toArray();
    }
}