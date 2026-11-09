<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_INITIAL_EMAIL', 'admin@catatduit.test');
        $adminPassword = env('ADMIN_INITIAL_PASSWORD', 'Admin#CatatDuit2026!');

        User::firstOrCreate(
            ['email' => strtolower(trim($adminEmail))],
            [
                'name' => 'Administrator',
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'status' => 'active',
                'timezone' => 'Asia/Jakarta',
                'locale' => 'id',
                'default_currency' => 'IDR',
                'theme_preference' => 'light',
                'email_verified_at' => Carbon::now('UTC'),
            ]
        );
    }
}
