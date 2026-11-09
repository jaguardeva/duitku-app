<?php

use App\Models\User;

test('redirects guest to login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('redirects authenticated and verified user to app dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('app.dashboard'));
});
