<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Choice extends Model
{
    /** @use HasFactory<\Database\Factories\ChoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'question_id',
        'label',
        'value',
        'score',
        'order',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'order' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
