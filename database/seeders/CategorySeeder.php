<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Expense Categories
            [
                'name' => 'Makanan & Minuman',
                'type' => 'expense',
                'icon' => 'utensils',
                'color_token' => 'amber',
                'sort_order' => 1,
            ],
            [
                'name' => 'Transportasi',
                'type' => 'expense',
                'icon' => 'car',
                'color_token' => 'blue',
                'sort_order' => 2,
            ],
            [
                'name' => 'Belanja',
                'type' => 'expense',
                'icon' => 'shopping-bag',
                'color_token' => 'purple',
                'sort_order' => 3,
            ],
            [
                'name' => 'Tagihan & Utilitas',
                'type' => 'expense',
                'icon' => 'receipt',
                'color_token' => 'orange',
                'sort_order' => 4,
            ],
            [
                'name' => 'Kesehatan',
                'type' => 'expense',
                'icon' => 'heart-pulse',
                'color_token' => 'rose',
                'sort_order' => 5,
            ],
            [
                'name' => 'Hiburan',
                'type' => 'expense',
                'icon' => 'film',
                'color_token' => 'indigo',
                'sort_order' => 6,
            ],
            [
                'name' => 'Pendidikan',
                'type' => 'expense',
                'icon' => 'graduation-cap',
                'color_token' => 'emerald',
                'sort_order' => 7,
            ],
            [
                'name' => 'Rumah',
                'type' => 'expense',
                'icon' => 'home',
                'color_token' => 'cyan',
                'sort_order' => 8,
            ],
            [
                'name' => 'Lainnya (Pengeluaran)',
                'type' => 'expense',
                'icon' => 'circle-ellipsis',
                'color_token' => 'slate',
                'sort_order' => 9,
            ],

            // Income Categories
            [
                'name' => 'Gaji',
                'type' => 'income',
                'icon' => 'wallet',
                'color_token' => 'emerald',
                'sort_order' => 1,
            ],
            [
                'name' => 'Bonus',
                'type' => 'income',
                'icon' => 'gift',
                'color_token' => 'amber',
                'sort_order' => 2,
            ],
            [
                'name' => 'Bisnis',
                'type' => 'income',
                'icon' => 'briefcase',
                'color_token' => 'blue',
                'sort_order' => 3,
            ],
            [
                'name' => 'Hadiah',
                'type' => 'income',
                'icon' => 'sparkles',
                'color_token' => 'rose',
                'sort_order' => 4,
            ],
            [
                'name' => 'Bunga & Investasi',
                'type' => 'income',
                'icon' => 'trending-up',
                'color_token' => 'cyan',
                'sort_order' => 5,
            ],
            [
                'name' => 'Lainnya (Pemasukan)',
                'type' => 'income',
                'icon' => 'plus-circle',
                'color_token' => 'slate',
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                [
                    'user_id' => null,
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                ],
                [
                    'icon' => $cat['icon'],
                    'color_token' => $cat['color_token'],
                    'is_system' => true,
                    'is_archived' => false,
                    'sort_order' => $cat['sort_order'],
                ]
            );
        }
    }
}
