<?php

namespace App\Enums;

enum QuestionType: string
{
    case SHORT_TEXT = 'short_text';
    case LONG_TEXT = 'long_text';
    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case NUMBER = 'number';
    case DATE = 'date';

    public function label(): string
    {
        return match($this) {
            self::SHORT_TEXT => 'Short Text',
            self::LONG_TEXT => 'Long Text',
            self::SINGLE_CHOICE => 'Single Choice',
            self::MULTIPLE_CHOICE => 'Multiple Choice',
            self::NUMBER => 'Number',
            self::DATE => 'Date',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SHORT_TEXT => 'Single line text input',
            self::LONG_TEXT => 'Multi-line text area',
            self::SINGLE_CHOICE => 'Radio buttons - select one option',
            self::MULTIPLE_CHOICE => 'Checkboxes - select multiple options',
            self::NUMBER => 'Numeric input field',
            self::DATE => 'Date picker input',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::SHORT_TEXT => 'type',
            self::LONG_TEXT => 'align-left',
            self::SINGLE_CHOICE => 'circle',
            self::MULTIPLE_CHOICE => 'check-square',
            self::NUMBER => 'hash',
            self::DATE => 'calendar',
        };
    }

    public function hasChoices(): bool
    {
        return in_array($this, [self::SINGLE_CHOICE, self::MULTIPLE_CHOICE]);
    }

    public function allowsMultipleAnswers(): bool
    {
        return $this === self::MULTIPLE_CHOICE;
    }

    public function inputType(): string
    {
        return match($this) {
            self::SHORT_TEXT => 'text',
            self::LONG_TEXT => 'textarea',
            self::SINGLE_CHOICE => 'radio',
            self::MULTIPLE_CHOICE => 'checkbox',
            self::NUMBER => 'number',
            self::DATE => 'date',
        };
    }

    public static function options(): array
    {
        return [
            self::SHORT_TEXT->value => self::SHORT_TEXT->label(),
            self::LONG_TEXT->value => self::LONG_TEXT->label(),
            self::SINGLE_CHOICE->value => self::SINGLE_CHOICE->label(),
            self::MULTIPLE_CHOICE->value => self::MULTIPLE_CHOICE->label(),
            self::NUMBER->value => self::NUMBER->label(),
            self::DATE->value => self::DATE->label(),
        ];
    }
}