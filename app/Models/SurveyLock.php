<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SurveyLock extends Model
{
    protected $fillable = [
        'lock_key',
        'process_id',
        'response_id',
        'operation_type',
        'acquired_at',
        'expires_at',
        'metadata'
    ];

    protected $casts = [
        'acquired_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Relationship with Response model
     */
    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    /**
     * Check if lock is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Acquire a lock for survey operation
     */
    public static function acquireLock(string $lockKey, int $responseId, string $operationType = 'submit', int $timeoutSeconds = 30): ?self
    {
        // Clean up expired locks first
        self::cleanupExpiredLocks();

        // Check if lock already exists and is not expired
        $existingLock = self::where('lock_key', $lockKey)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingLock) {
            return null; // Lock already acquired
        }

        // Create new lock
        return self::create([
            'lock_key' => $lockKey,
            'process_id' => getmypid(),
            'response_id' => $responseId,
            'operation_type' => $operationType,
            'acquired_at' => now(),
            'expires_at' => now()->addSeconds($timeoutSeconds),
            'metadata' => [
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip()
            ]
        ]);
    }

    /**
     * Release a lock
     */
    public static function releaseLock(string $lockKey): bool
    {
        return self::where('lock_key', $lockKey)->delete() > 0;
    }

    /**
     * Clean up expired locks
     */
    public static function cleanupExpiredLocks(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Get queue position for a lock key
     */
    public static function getQueuePosition(string $lockKey): int
    {
        $locks = self::where('expires_at', '>', now())
            ->orderBy('acquired_at')
            ->pluck('lock_key')
            ->toArray();

        $position = array_search($lockKey, $locks);
        return $position !== false ? $position + 1 : 0;
    }
}
