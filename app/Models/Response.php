<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'meta' => 'array',
    ];

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
