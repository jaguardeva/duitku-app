<?php

namespace App\Models;

use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $type
 * @property string $currency_code
 * @property string $opening_balance
 * @property string|null $icon
 * @property string|null $color_token
 * @property bool $is_archived
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Wallet extends Model
{
    /** @use HasFactory<WalletFactory> */
    use HasFactory, HasUuids;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'currency_code',
        'opening_balance',
        'icon',
        'color_token',
        'is_archived',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:4',
            'is_archived' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function transfersAsSource(): HasMany
    {
        return $this->hasMany(Transfer::class, 'source_wallet_id');
    }

    /**
     * @return HasMany<Transfer, $this>
     */
    public function transfersAsDestination(): HasMany
    {
        return $this->hasMany(Transfer::class, 'destination_wallet_id');
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_archived', false);
    }
}
