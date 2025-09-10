<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultCategoryRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'result_category_id',
        'operation',
        'title',
        'score',
        'color',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'operation' => 'string',
    ];

    public function resultCategory(): BelongsTo
    {
        return $this->belongsTo(ResultCategory::class);
    }
}
