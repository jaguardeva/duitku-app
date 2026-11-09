<?php

namespace Database\Factories;

use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transfer>
 */
class TransferFactory extends Factory
{
    protected $model = Transfer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'source_wallet_id' => Wallet::factory(),
            'destination_wallet_id' => Wallet::factory(),
            'amount' => 100000,
            'currency_code' => 'IDR',
            'note' => 'Transfer antar dompet',
            'transfer_date' => now()->toDateString(),
            'transfer_time' => now()->format('H:i:s'),
        ];
    }
}
