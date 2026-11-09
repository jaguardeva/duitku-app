<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Dompet Utama', 'BCA Pribadi', 'Mandiri Tabungan', 'Gopay', 'OVO']),
            'type' => 'bank',
            'currency_code' => 'IDR',
            'opening_balance' => 1000000,
            'color_token' => 'blue',
            'icon' => 'wallet',
            'is_archived' => false,
            'sort_order' => 0,
        ];
    }
}
