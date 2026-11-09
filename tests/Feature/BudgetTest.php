<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->expenseCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'expense',
        'name' => 'Belanja Bulanan',
    ]);
});

test('budget index page can be rendered', function () {
    Budget::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 1000000,
    ]);

    $response = $this->actingAs($this->user)->get(route('budgets.index'));

    $response->assertOk();
});

test('user can create a monthly category budget', function () {
    $response = $this->actingAs($this->user)->post(route('budgets.store'), [
        'category_id' => $this->expenseCategory->id,
        'name' => 'Anggaran Belanja',
        'amount' => 1200000,
        'currency_code' => 'IDR',
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
        'warning_threshold_percent' => 80,
    ]);

    $response->assertRedirect(route('budgets.index'));

    $this->assertDatabaseHas('budgets', [
        'user_id' => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 1200000,
        'currency_code' => 'IDR',
        'warning_threshold_percent' => 80,
    ]);
});

test('user can create a budget without currency code and it defaults to IDR', function () {
    $response = $this->actingAs($this->user)->post(route('budgets.store'), [
        'category_id' => $this->expenseCategory->id,
        'name' => 'Anggaran Transportasi',
        'amount' => 500000,
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
    ]);

    $response->assertRedirect(route('budgets.index'));

    $this->assertDatabaseHas('budgets', [
        'user_id' => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'name' => 'Anggaran Transportasi',
        'amount' => 500000,
        'currency_code' => 'IDR',
    ]);
});

test('cannot create budget with non-expense category', function () {
    $incomeCategory = Category::factory()->create([
        'user_id' => $this->user->id,
        'type' => 'income',
        'name' => 'Gaji',
    ]);

    $response = $this->actingAs($this->user)->post(route('budgets.store'), [
        'category_id' => $incomeCategory->id,
        'amount' => 500000,
        'currency_code' => 'IDR',
        'period_start' => now()->startOfMonth()->toDateString(),
        'period_end' => now()->endOfMonth()->toDateString(),
    ]);

    $response->assertSessionHasErrors('category_id');
});

test('user can update their budget amount and warning threshold', function () {
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 1000000,
        'warning_threshold_percent' => 80,
    ]);

    $response = $this->actingAs($this->user)->put(route('budgets.update', $budget), [
        'amount' => 1500000,
        'warning_threshold_percent' => 90,
        'is_active' => true,
    ]);

    $response->assertRedirect(route('budgets.index'));

    $budget->refresh();
    expect((float) $budget->amount)->toBe(1500000.0);
    expect($budget->warning_threshold_percent)->toBe(90);
});

test('user can delete their budget', function () {
    $budget = Budget::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->expenseCategory->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('budgets.destroy', $budget));

    $response->assertRedirect(route('budgets.index'));
    $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
});

test('user cannot update or delete another user budget', function () {
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
    $otherBudget = Budget::factory()->create([
        'user_id' => $otherUser->id,
        'category_id' => $otherCategory->id,
    ]);

    $response = $this->actingAs($this->user)->delete(route('budgets.destroy', $otherBudget));

    $response->assertForbidden();
});
