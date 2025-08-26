<?php

namespace App\Enums;

class EnumHelper
{
    /**
     * Get all available user roles
     */
    public static function getUserRoles(): array
    {
        return UserRole::options();
    }

    /**
     * Get all available survey statuses
     */
    public static function getSurveyStatuses(): array
    {
        return SurveyStatus::options();
    }

    /**
     * Get all available survey visibility options
     */
    public static function getSurveyVisibilities(): array
    {
        return SurveyVisibility::options();
    }

    /**
     * Get all available question types
     */
    public static function getQuestionTypes(): array
    {
        return QuestionType::options();
    }

    /**
     * Get all available response statuses
     */
    public static function getResponseStatuses(): array
    {
        return ResponseStatus::options();
    }

    /**
     * Get survey status with color information
     */
    public static function getSurveyStatusWithColors(): array
    {
        $statuses = [];
        foreach (SurveyStatus::cases() as $status) {
            $statuses[$status->value] = [
                'label' => $status->label(),
                'color' => $status->color(),
                'description' => $status->description(),
            ];
        }
        return $statuses;
    }

    /**
     * Get survey visibility with icons
     */
    public static function getSurveyVisibilityWithIcons(): array
    {
        $visibilities = [];
        foreach (SurveyVisibility::cases() as $visibility) {
            $visibilities[$visibility->value] = [
                'label' => $visibility->label(),
                'icon' => $visibility->icon(),
                'description' => $visibility->description(),
            ];
        }
        return $visibilities;
    }

    /**
     * Get question types with icons and input types
     */
    public static function getQuestionTypesWithDetails(): array
    {
        $types = [];
        foreach (QuestionType::cases() as $type) {
            $types[$type->value] = [
                'label' => $type->label(),
                'icon' => $type->icon(),
                'description' => $type->description(),
                'input_type' => $type->inputType(),
                'has_choices' => $type->hasChoices(),
                'allows_multiple' => $type->allowsMultipleAnswers(),
            ];
        }
        return $types;
    }

    /**
     * Get response statuses with colors
     */
    public static function getResponseStatusesWithColors(): array
    {
        $statuses = [];
        foreach (ResponseStatus::cases() as $status) {
            $statuses[$status->value] = [
                'label' => $status->label(),
                'color' => $status->color(),
                'description' => $status->description(),
                'is_complete' => $status->isComplete(),
                'can_be_modified' => $status->canBeModified(),
            ];
        }
        return $statuses;
    }
}