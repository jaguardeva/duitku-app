<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'theme_preference' => 'dark',
    ]);
});

test('profile page no longer exposes theme preference settings', function () {
    $response = $this->actingAs($this->user)->get(route('profile.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('app/profile/Index')
        ->where('user.name', $this->user->name)
        ->missing('user.theme_preference')
    );
});

test('updating profile information preserves the existing theme preference', function () {
    $response = $this->actingAs($this->user)->put(route('profile.update'), [
        'name' => 'Updated User Name',
        'timezone' => 'Asia/Jakarta',
        'locale' => 'id',
        'default_currency' => 'IDR',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    expect($this->user->fresh()->theme_preference)->toBe('dark');
});
