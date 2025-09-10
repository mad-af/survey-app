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
        'owner_type',
        'owner_id',
        'name',
        'description',
    ];

    protected $casts = [
        'owner_type' => 'string',
    ];

    public function owner()
    {
        return $this->morphTo();
    }

    public function survey()
    {
        return $this->belongsTo(Survey::class, 'owner_id')->where('owner_type', 'survey');
    }

    public function surveySection()
    {
        return $this->belongsTo(SurveySection::class, 'owner_id')->where('owner_type', 'survey_section');
    }

    public function rules()
    {
        return $this->hasMany(ResultCategoryRule::class);
    }

    public function responseScores(): HasMany
    {
        return $this->hasMany(ResponseScore::class);
    }
}
