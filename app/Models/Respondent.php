<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'location_id',
        'consent_at',
    ];

    protected function casts(): array
    {
        return [
            'consent_at' => 'datetime',
            'birth_year' => 'integer',
        ];
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
