<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $code_hash
 * @property Carbon $expires_at
 * @property int $attempts
 * @property int $max_attempts
 * @property Carbon $sent_at
 * @property Carbon|null $consumed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmailVerificationOtp extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code_hash',
        'expires_at',
        'attempts',
        'max_attempts',
        'sent_at',
        'consumed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'consumed_at' => 'datetime',
            'attempts' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return Carbon::now('UTC')->isAfter($this->expires_at);
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= $this->max_attempts;
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isConsumed() && ! $this->hasExceededAttempts();
    }
}
