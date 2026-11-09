<?php

use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BalanceCalculationService;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->walletA = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'IDR',
        'opening_balance' => 1000000,
        'name' => 'BCA',
    ]);

    $this->walletB = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'IDR',
        'opening_balance' => 200000,
        'name' => 'GoPay',
    ]);
});

test('transfer index page can be rendered', function () {
    Transfer::factory()->create([
        'user_id' => $this->user->id,
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 150000,
        'currency_code' => 'IDR',
    ]);

    $response = $this->actingAs($this->user)->get(route('transfers.index'));

    $response->assertOk();
});

test('transfer index exposes calculated wallet balances', function () {
    $response = $this->actingAs($this->user)->get(route('transfers.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('app/transfers/Index')
        ->where('wallets.0.id', $this->walletA->id)
        ->where('wallets.0.current_balance', '1000000.0000')
    );
});

test('user can transfer funds between two wallets with same currency', function () {
    $response = $this->actingAs($this->user)->post(route('transfers.store'), [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 300000,
        'transfer_date' => now()->toDateString(),
        'transfer_time' => '14:00',
        'note' => 'Top up GoPay dari BCA',
    ]);

    $response->assertRedirect(route('transfers.index'));

    $this->assertDatabaseHas('transfers', [
        'user_id' => $this->user->id,
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 300000,
        'currency_code' => 'IDR',
        'note' => 'Top up GoPay dari BCA',
    ]);

    $calc = app(BalanceCalculationService::class);
    // Wallet A: 1,000,000 - 300,000 = 700,000
    expect((float) $calc->calculateWalletBalance($this->walletA))->toBe(700000.0);
    // Wallet B: 200,000 + 300,000 = 500,000
    expect((float) $calc->calculateWalletBalance($this->walletB))->toBe(500000.0);
});

test('transfer is rejected when amount exceeds source wallet balance', function () {
    $response = $this->actingAs($this->user)->post(route('transfers.store'), [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 1000001,
        'transfer_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors([
        'amount' => 'Saldo dompet asal tidak mencukupi untuk transfer ini.',
    ]);
    $this->assertDatabaseMissing('transfers', [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 1000001,
    ]);
});

test('transfer source and destination wallet cannot be the same', function () {
    $response = $this->actingAs($this->user)->post(route('transfers.store'), [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletA->id,
        'amount' => 50000,
        'transfer_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('source_wallet_id');
});

test('transfer rejected between wallets with different currencies', function () {
    $usdWallet = Wallet::factory()->create([
        'user_id' => $this->user->id,
        'currency_code' => 'USD',
        'opening_balance' => 50,
    ]);

    $response = $this->actingAs($this->user)->post(route('transfers.store'), [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $usdWallet->id,
        'amount' => 100000,
        'transfer_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('destination_wallet_id');
});

test('cannot transfer from or to another user wallet', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherWallet = Wallet::factory()->create([
        'user_id' => $otherUser->id,
        'currency_code' => 'IDR',
    ]);

    $response = $this->actingAs($this->user)->post(route('transfers.store'), [
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $otherWallet->id,
        'amount' => 50000,
        'transfer_date' => now()->toDateString(),
    ]);

    $response->assertSessionHasErrors('destination_wallet_id');
});

test('user can delete transfer and both wallet ledger balances are restored', function () {
    $transfer = Transfer::factory()->create([
        'user_id' => $this->user->id,
        'source_wallet_id' => $this->walletA->id,
        'destination_wallet_id' => $this->walletB->id,
        'amount' => 200000,
        'currency_code' => 'IDR',
    ]);

    $calc = app(BalanceCalculationService::class);
    // Before delete: A = 800,000, B = 400,000
    expect((float) $calc->calculateWalletBalance($this->walletA))->toBe(800000.0);
    expect((float) $calc->calculateWalletBalance($this->walletB))->toBe(400000.0);

    $response = $this->actingAs($this->user)->delete(route('transfers.destroy', $transfer));

    $response->assertRedirect(route('transfers.index'));
    $this->assertSoftDeleted('transfers', ['id' => $transfer->id]);

    // After delete: A = 1,000,000, B = 200,000
    expect((float) $calc->calculateWalletBalance($this->walletA))->toBe(1000000.0);
    expect((float) $calc->calculateWalletBalance($this->walletB))->toBe(200000.0);
});

test('user cannot delete another user transfer', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherWallet1 = Wallet::factory()->create(['user_id' => $otherUser->id]);
    $otherWallet2 = Wallet::factory()->create(['user_id' => $otherUser->id]);

    $otherTransfer = Transfer::factory()->create([
        'user_id' => $otherUser->id,
        'source_wallet_id' => $otherWallet1->id,
        'destination_wallet_id' => $otherWallet2->id,
        'currency_code' => $otherWallet1->currency_code,
    ]);

    $response = $this->actingAs($this->user)->delete(route('transfers.destroy', $otherTransfer));

    $response->assertForbidden();
});
