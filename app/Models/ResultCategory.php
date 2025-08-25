<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultCategory extends Model
{
    /** @use HasFactory<\Database\Factories\ResultCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'name',
        'description',
        'min_score',
        'max_score',
        'color',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function responseScores(): HasMany
    {
        return $this->hasMany(ResponseScore::class);
    }
}
