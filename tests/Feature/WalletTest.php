<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BalanceCalculationService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);
});

test('wallet index page can be rendered', function () {
    Wallet::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'BCA Utama',
    ]);

    $response = $this->actingAs($this->user)->get(route('wallets.index'));

    $response->assertOk();
});

test('user can create a wallet with initial balance and pocket attributes', function () {
    $response = $this->actingAs($this->user)->post(route('wallets.store'), [
        'name' => 'GoPay Tabungan',
        'type' => 'e_wallet',
        'currency_code' => 'IDR',
        'opening_balance' => 250000,
        'color_token' => 'emerald',
        'icon' => 'smartphone',
    ]);

    $response->assertRedirect(route('wallets.index'));

    $this->assertDatabaseHas('wallets', [
        'user_id' => $this->user->id,
        'name' => 'GoPay Tabungan',
        'type' => 'e_wallet',
        'currency_code' => 'IDR',
        'color_token' => 'emerald',
        'icon' => 'smartphone',
    ]);
});

test('user can create a wallet without currency code and it defaults to IDR', function () {
    $response = $this->actingAs($this->user)->post(route('wallets.store'), [
        'name' => 'BCA Tunai',
        'type' => 'bank',
        'opening_balance' => 100000,
    ]);

    $response->assertRedirect(route('wallets.index'));

    $this->assertDatabaseHas('wallets', [
        'user_id' => $this->user->id,
        'name' => 'BCA Tunai',
        'type' => 'bank',
        'currency_code' => 'IDR',
        'opening_balance' => 100000,
    ]);
});

test('wallet creation validates required fields', function () {
    $response = $this->actingAs($this->user)->post(route('wallets.store'), [
        'name' => '',
        'type' => 'invalid_type',
        'currency_code' => 'INVALID_CURRENCY',
        'opening_balance' => -100,
    ]);

    $response->assertSessionHasErrors(['name', 'type', 'currency_code', 'opening_balance']);
});

test('wallet currency cannot be changed on update', function () {
    $wallet = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'bank',
        'currency_code' => 'IDR',
        'name' => 'Nama Lama',
    ]);

    $response = $this->actingAs($this->user)->put(route('wallets.update', $wallet), [
        'name' => 'Nama Baru',
        'type' => 'bank',
        'currency_code' => 'USD', // Should be ignored or not changed
        'color_token' => 'blue',
        'icon' => 'credit-card',
    ]);

    $response->assertRedirect(route('wallets.index'));

    $wallet->refresh();
    expect($wallet->name)->toBe('Nama Baru');
    expect($wallet->currency_code)->toBe('IDR');
    expect($wallet->color_token)->toBe('blue');
});

test('wallet cannot be deleted if transactions exist', function () {
    $wallet = Wallet::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $category = Category::factory()->create(['user_id' => $this->user->id]);

    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('wallets.destroy', $wallet));

    $response->assertSessionHasErrors('wallet');
    $this->assertDatabaseHas('wallets', ['id' => $wallet->id]);
});

test('wallet cannot be deleted if transfers exist', function () {
    $walletA = Wallet::factory()->create(['user_id' => $this->user->id]);
    $walletB = Wallet::factory()->create(['user_id' => $this->user->id]);

    Transfer::factory()->create([
        'user_id' => $this->user->id,
        'source_wallet_id' => $walletA->id,
        'destination_wallet_id' => $walletB->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('wallets.destroy', $walletA));

    $response->assertSessionHasErrors('wallet');
    $this->assertDatabaseHas('wallets', ['id' => $walletA->id]);
});

test('wallet can be deleted if no transactions or transfers exist', function () {
    $wallet = Wallet::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('wallets.destroy', $wallet));

    $response->assertRedirect(route('wallets.index'));
    $this->assertDatabaseMissing('wallets', ['id' => $wallet->id]);
});

test('user cannot view or update another user wallet', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($this->user)->put(route('wallets.update', $otherWallet), [
        'name' => 'Hacked',
        'type' => 'bank',
        'color_token' => 'slate',
        'icon' => 'wallet',
    ]);

    $response->assertForbidden();
});

test('ledger dynamic balance calculates correctly including transactions and transfers', function () {
    $walletA = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'IDR',
        'opening_balance' => 1000000,
    ]);

    $walletB = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'IDR',
        'opening_balance' => 500000,
    ]);

    $category = Category::factory()->create(['user_id' => $this->user->id]);

    // Income +500,000 on walletA
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $walletA->id,
        'category_id' => $category->id,
        'type' => 'income',
        'amount' => 500000,
        'currency_code' => 'IDR',
    ]);

    // Expense -200,000 on walletA
    Transaction::factory()->create([
        'user_id' => $this->user->id,
        'wallet_id' => $walletA->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 200000,
        'currency_code' => 'IDR',
    ]);

    // Transfer 300,000 from walletA to walletB
    Transfer::factory()->create([
        'user_id' => $this->user->id,
        'source_wallet_id' => $walletA->id,
        'destination_wallet_id' => $walletB->id,
        'amount' => 300000,
        'currency_code' => 'IDR',
    ]);

    /** @var BalanceCalculationService $calc */
    $calc = app(BalanceCalculationService::class);

    // Expected walletA: 1,000,000 + 500,000 - 200,000 - 300,000 = 1,000,000
    $balanceA = $calc->calculateWalletBalance($walletA);
    expect((float) $balanceA)->toBe(1000000.0);

    // Expected walletB: 500,000 + 300,000 = 800,000
    $balanceB = $calc->calculateWalletBalance($walletB);
    expect((float) $balanceB)->toBe(800000.0);
});
