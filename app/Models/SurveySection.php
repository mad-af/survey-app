<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveySection extends Model
{
    /** @use HasFactory<\Database\Factories\SurveySectionFactory> */
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'title',
        'description',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'section_id')->orderBy('order');
    }
}
