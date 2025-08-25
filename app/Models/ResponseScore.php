<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseScore extends Model
{
    /** @use HasFactory<\Database\Factories\ResponseScoreFactory> */
    use HasFactory;

    protected $fillable = [
        'response_id',
        'result_category_id',
        'total_score',
        'max_possible_score',
        'percentage',
        'section_scores',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
        'max_possible_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'section_scores' => 'array',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function resultCategory(): BelongsTo
    {
        return $this->belongsTo(ResultCategory::class);
    }
}
