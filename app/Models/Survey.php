<?php

namespace App\Models;

use App\Enums\SurveyStatus;
use App\Enums\SurveyVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    /** @use HasFactory<\Database\Factories\SurveyFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'code',
        'title',
        'description',
        'status',
        'is_anonymous',
        'visibility',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'status' => SurveyStatus::class,
        'visibility' => SurveyVisibility::class,
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(SurveySection::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function respondents(): HasMany
    {
        return $this->hasMany(SurveyRespondent::class);
    }

    public function resultCategories(): HasMany
    {
        return $this->hasMany(ResultCategory::class);
    }
}
