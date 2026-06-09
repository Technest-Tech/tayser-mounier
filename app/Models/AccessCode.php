<?php

namespace App\Models;

use App\Enums\AccessCodeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'batch_id',
        'code_hash',
        'status',
        'redeemed_by',
        'redeemed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccessCodeStatus::class,
            'redeemed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('status', AccessCodeStatus::Unused);
    }

    /**
     * Deterministically hash a plaintext code for storage/lookup.
     *
     * HMAC-SHA256 keyed with the app key: codes are never stored in plaintext,
     * yet a given code always maps to the same hash so we can look it up.
     */
    public static function hashCode(string $plain): string
    {
        return hash_hmac('sha256', mb_strtoupper(trim($plain)), config('app.key'));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRedeemable(): bool
    {
        return $this->status === AccessCodeStatus::Unused && ! $this->isExpired();
    }
}
