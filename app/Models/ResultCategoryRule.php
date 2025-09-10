<?php

namespace App\Models;

use App\Enums\OperationType;
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
        'operation' => OperationType::class,
    ];

    public function resultCategory(): BelongsTo
    {
        return $this->belongsTo(ResultCategory::class);
    }
}
