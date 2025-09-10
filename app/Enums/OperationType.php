<?php

namespace App\Enums;

enum OperationType: string
{
    case LT = 'lt';
    case GT = 'gt';
    case ELSE = 'else';

    public function label(): string
    {
        return match($this) {
            self::LT => 'Less Than',
            self::GT => 'Greater Than',
            self::ELSE => 'Default/Else',
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