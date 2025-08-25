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
}
