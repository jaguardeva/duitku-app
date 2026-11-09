<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update any existing records to IDR
        DB::table('users')->where('default_currency', '!=', 'IDR')->orWhereNull('default_currency')->update(['default_currency' => 'IDR']);
        DB::table('wallets')->where('currency_code', '!=', 'IDR')->orWhereNull('currency_code')->update(['currency_code' => 'IDR']);
        DB::table('transactions')->where('currency_code', '!=', 'IDR')->orWhereNull('currency_code')->update(['currency_code' => 'IDR']);
        DB::table('transfers')->where('currency_code', '!=', 'IDR')->orWhereNull('currency_code')->update(['currency_code' => 'IDR']);
        DB::table('budgets')->where('currency_code', '!=', 'IDR')->orWhereNull('currency_code')->update(['currency_code' => 'IDR']);

        // 2. Set default('IDR') on all currency columns
        Schema::table('users', function (Blueprint $table): void {
            $table->char('default_currency', 3)->default('IDR')->change();
        });

        Schema::table('wallets', function (Blueprint $table): void {
            $table->char('currency_code', 3)->default('IDR')->change();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->char('currency_code', 3)->default('IDR')->change();
        });

        Schema::table('transfers', function (Blueprint $table): void {
            $table->char('currency_code', 3)->default('IDR')->change();
        });

        Schema::table('budgets', function (Blueprint $table): void {
            $table->char('currency_code', 3)->default('IDR')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table): void {
            $table->char('currency_code', 3)->change();
        });

        Schema::table('transfers', function (Blueprint $table): void {
            $table->char('currency_code', 3)->change();
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->char('currency_code', 3)->change();
        });

        Schema::table('wallets', function (Blueprint $table): void {
            $table->char('currency_code', 3)->change();
        });
    }
};
