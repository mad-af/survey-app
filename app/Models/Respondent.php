<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Respondent extends Model
{
    /** @use HasFactory<\Database\Factories\RespondentFactory> */
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'email',
        'phone',
        'gender',
        'birth_year',
        'organization',
        'department',
        'role_title',
        'location',
        'demographics',
        'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'demographics' => 'array',
            'consent_at' => 'datetime',
            'birth_year' => 'integer',
        ];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function surveyEnrollments(): HasMany
    {
        return $this->hasMany(SurveyRespondent::class);
    }
}
