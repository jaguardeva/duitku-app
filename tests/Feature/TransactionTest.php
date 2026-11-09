<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BalanceCalculationService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->wallet = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'IDR',
        'opening_balance' => 1000000,
    ]);

    $this->expenseCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'expense',
        'name' => 'Makan Siang',
    ]);

    $this->incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'income',
        'name' => 'Gaji Pokok',
    ]);
});

test('transactions index page can be rendered with filter query params', function () {
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'title' => 'Nasi Padang',
        'type' => 'expense',
        'amount' => 35000,
    ]);

    $response = $this->actingAs($this->user)->get(route('transactions.index', [
        'search' => 'Padang',
        'type' => 'expense',
    ]));

    $response->assertOk();
});

test('user can record an expense transaction', function () {
    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 45000,
        'title' => 'Kopi Latte',
        'notes' => 'Coffee break sore',
        'transaction_date' => now()->toDateString(),
        'transaction_time' => '15:30',
    ]);

    $response->assertRedirect(route('transactions.index'));

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 45000,
        'currency_code' => 'IDR',
        'title' => 'Kopi Latte',
    ]);

    // Ledger balance should now be 1,000,000 - 45,000 = 955,000
    $calc = app(BalanceCalculationService::class);
    expect((float) $calc->calculateWalletBalance($this->wallet))->toBe(955000.0);
});

test('user can record an income transaction', function () {
    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->incomeCategory->id,
        'type' => 'income',
        'amount' => 2000000,
        'title' => 'Bonus Proyek',
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'));

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->incomeCategory->id,
        'type' => 'income',
        'amount' => 2000000,
        'currency_code' => 'IDR',
    ]);

    $calc = app(BalanceCalculationService::class);
    expect((float) $calc->calculateWalletBalance($this->wallet))->toBe(3000000.0);
});

test('transaction amount must be greater than zero', function () {
    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 0,
        'title' => 'Zero Amount',
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('amount');
});

test('transaction rejects category with mismatched type', function () {
    // Attempting to use income category with expense type
    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->incomeCategory->id,
        'type' => 'expense',
        'amount' => 50000,
        'title' => 'Mismatch Test',
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('category_id');
});

test('cannot record transaction on another user wallet', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($this->user)->post(route('transactions.store'), [
        'wallet_id' => $otherWallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 50000,
        'title' => 'Hacked Transaction',
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('wallet_id');
});

test('user can update their transaction', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 50000,
        'title' => 'Makan Pagi',
    ]);

    $response = $this->actingAs($this->user)->put(route('transactions.update', $transaction), [
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 75000,
        'title' => 'Makan Pagi Lengkap',
        'transaction_date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('transactions.index'));

    $transaction->refresh();
    expect((float) $transaction->amount)->toBe(75000.0);
    expect($transaction->title)->toBe('Makan Pagi Lengkap');
});

test('user can delete transaction and ledger balance reflects soft delete', function () {
    $transaction = Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $this->wallet->id,
        'category_id' => $this->expenseCategory->id,
        'type' => 'expense',
        'amount' => 100000,
    ]);

    $calc = app(BalanceCalculationService::class);
    expect((float) $calc->calculateWalletBalance($this->wallet))->toBe(900000.0);

    $response = $this->actingAs($this->user)->delete(route('transactions.destroy', $transaction));

    $response->assertRedirect(route('transactions.index'));

    // Check soft delete
    $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);

    // Ledger balance should restore back to 1,000,000
    expect((float) $calc->calculateWalletBalance($this->wallet))->toBe(1000000.0);
});

test('user cannot update or delete another user transaction', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

    $otherTransaction = Transaction::factory()->create([
        'user_id' => $otherUser->id,
        'wallet_id' => $otherWallet->id,
        'category_id' => $otherCategory->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('transactions.destroy', $otherTransaction));

    $response->assertForbidden();
});
