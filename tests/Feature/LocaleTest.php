<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;

test('a guest can change the session locale', function () {
    $response = $this->from('/login')->post(route('locale.update'), [
        'locale' => 'en',
    ]);

    $response->assertRedirect('/login');
    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('catatduit_locale', 'en');
});

test('invalid locales are rejected', function () {
    $response = $this->post(route('locale.update'), [
        'locale' => 'fr',
    ]);

    $response->assertSessionHasErrors('locale');
});

test('an authenticated user changing locale updates user record and queues cookie', function () {
    $user = User::factory()->create([
        'locale' => 'id',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('locale.update'), [
        'locale' => 'en',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('catatduit_locale', 'en');
    expect($user->fresh()->locale)->toBe('en');
});

test('an authenticated user locale is applied to validation messages', function () {
    $user = User::factory()->create([
        'locale' => 'en',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => '',
        'timezone' => 'Asia/Jakarta',
        'locale' => 'en',
    ]);

    $response->assertSessionHasErrors([
        'name' => __('validation.required', ['attribute' => 'name']),
    ]);
});

test('updating profile locale persists it in the session, cookie, and user record', function () {
    $user = User::factory()->create([
        'locale' => 'id',
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'timezone' => 'Asia/Jakarta',
        'locale' => 'en',
        'default_currency' => 'IDR',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('locale', 'en');
    $response->assertCookie('catatduit_locale', 'en');
    expect($user->fresh()->locale)->toBe('en');
});

test('registering a user inherits the chosen session locale', function () {
    $action = app(CreateNewUser::class);

    session(['locale' => 'en']);

    $user = $action->create([
        'name' => 'John English',
        'email' => 'john.english@example.com',
        'password' => 'Secret123!@#',
        'password_confirmation' => 'Secret123!@#',
    ]);

    expect($user->locale)->toBe('en');
});
