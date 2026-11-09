<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'wallet_id' => Wallet::factory(),
            'category_id' => Category::factory(),
            'type' => 'expense',
            'amount' => 50000,
            'currency_code' => 'IDR',
            'title' => fake()->sentence(3),
            'notes' => fake()->sentence(),
            'transaction_date' => now()->toDateString(),
            'transaction_time' => now()->format('H:i:s'),
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => 'income',
        ]);
    }
}
