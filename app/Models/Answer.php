<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    /** @use HasFactory<\Database\Factories\AnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'response_id',
        'question_id',
        'choice_id',
        'value_text',
        'value_number',
        'value_json',
    ];

    protected $casts = [
        'value_number' => 'decimal:2',
        'value_json' => 'array',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function choice(): BelongsTo
    {
        return $this->belongsTo(Choice::class);
    }
}
