<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Makanan & Minuman', 'Transportasi', 'Belanja', 'Gaji', 'Bonus']),
            'type' => 'expense',
            'icon' => 'shopping-cart',
            'color_token' => 'red',
            'is_system' => false,
            'is_archived' => false,
            'sort_order' => 0,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => 'income',
            'name' => 'Gaji Pokok',
            'icon' => 'banknote',
            'color_token' => 'emerald',
        ]);
    }

    public function system(): static
    {
        return $this->state(fn () => [
            'user_id' => null,
            'is_system' => true,
        ]);
    }
}
