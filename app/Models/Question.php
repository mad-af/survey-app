<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'section_id',
        'text',
        'type',
        'required',
        'order',
        'score_weight',
    ];

    protected $casts = [
        'required' => 'boolean',
        'order' => 'integer',
        'score_weight' => 'decimal:2',
        'type' => QuestionType::class,
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SurveySection::class, 'section_id');
    }

    public function choices(): HasMany
    {
        return $this->hasMany(Choice::class)->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
