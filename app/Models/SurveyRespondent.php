<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyRespondent extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyRespondentFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'respondent_id',
        'status',
        'invited_at',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function respondent(): BelongsTo
    {
        return $this->belongsTo(Respondent::class);
    }

    /**
     * Update status to started
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'started',
            'started_at' => $this->started_at ?? now()
        ]);
    }

    /**
     * Update status to in_progress
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => $this->started_at ?? now()
        ]);
    }

    /**
     * Update status to completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    /**
     * Create or update survey respondent relationship
     */
    public static function createOrUpdateRelationship(int $surveyId, int $respondentId, string $status = 'in_progress'): self
    {
        return self::updateOrCreate(
            [
                'survey_id' => $surveyId,
                'respondent_id' => $respondentId
            ],
            [
                'status' => $status,
                'invited_at' => now(),
                'started_at' => in_array($status, ['started', 'in_progress', 'completed']) ? now() : null,
                'completed_at' => $status === 'completed' ? now() : null
            ]
        );
    }

    /**
     * Check if respondent can take survey
     */
    public function canTakeSurvey(): bool
    {
        return in_array($this->status, ['invited', 'started', 'in_progress']) && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
