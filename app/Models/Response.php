<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\ResponseStatus;

class Response extends Model
{
    /** @use HasFactory<\Database\Factories\ResponseFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'respondent_id',
        'respondent_token',
        'started_at',
        'submitted_at',
        'meta',
        'current_step',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'meta' => 'array',
        'current_step' => 'integer',
        'status' => ResponseStatus::class,
    ];

    // Step constants
    const STEP_RESPONDENT_DATA = 1;
    const STEP_QUESTIONS = 2;
    const STEP_RESULT = 3;

    // Status constants
    const STATUS_STARTED = 'started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ABANDONED = 'abandoned';

    /**
     * Get step name from step number
     */
    public function getStepNameAttribute(): string
    {
        return match($this->current_step) {
            self::STEP_RESPONDENT_DATA => 'RespondentData',
            self::STEP_QUESTIONS => 'Questions',
            self::STEP_RESULT => 'Result',
            default => 'Unknown'
        };
    }

    /**
     * Check if response is at specific step
     */
    public function isAtStep(int $step): bool
    {
        return $this->current_step === $step;
    }

    /**
     * Move to next step
     */
    public function moveToNextStep(): bool
    {
        if ($this->current_step < self::STEP_RESULT) {
            $this->current_step++;
            return $this->save();
        }
        return false;
    }

    /**
     * Set current step
     */
    public function setCurrentStep(int $step): bool
    {
        if ($step >= self::STEP_RESPONDENT_DATA && $step <= self::STEP_RESULT) {
            $this->current_step = $step;
            return $this->save();
        }
        return false;
    }

    // Status helper methods
    public function isStarted(): bool
    {
        return $this->status === ResponseStatus::STARTED;
    }

    public function isInProgress(): bool
    {
        return $this->status === ResponseStatus::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === ResponseStatus::COMPLETED;
    }

    public function isAbandoned(): bool
    {
        return $this->status === ResponseStatus::ABANDONED;
    }

    public function canBeModified(): bool
    {
        return $this->status->canBeModified();
    }

    public function markAsInProgress(): void
    {
        $this->status = ResponseStatus::IN_PROGRESS;
        $this->save();
    }

    public function markAsCompleted(): void
    {
        $this->status = ResponseStatus::COMPLETED;
        $this->submitted_at = now();
        $this->save();
    }

    public function markAsAbandoned(): void
    {
        $this->status = ResponseStatus::ABANDONED;
        $this->save();
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function score(): HasOne
    {
        return $this->hasOne(ResponseScore::class);
    }
}
