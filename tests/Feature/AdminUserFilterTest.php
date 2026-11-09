<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->adminUser = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->regularUser = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'user',
        'status' => 'active',
    ]);
});

test('unauthenticated user cannot access admin users list', function () {
    $response = $this->get(route('admin.users.index'));

    $response->assertRedirect(route('login'));
});

test('regular user cannot access admin users list', function () {
    $response = $this->actingAs($this->regularUser)->get(route('admin.users.index'));

    $response->assertForbidden();
});

test('admin can view users list with pagination meta and default sorting', function () {
    User::factory()->create([
        'role' => 'user',
        'name' => 'Budi Santoso',
        'created_at' => now()->addMinutes(1),
    ]);

    User::factory()->create([
        'role' => 'user',
        'name' => 'Citra Dewi',
        'created_at' => now()->addMinutes(5),
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.users.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->has('users.data')
        ->where('users.per_page', 15)
        ->where('filters.sort_by', 'created_at')
        ->where('filters.sort_direction', 'desc')
        ->where('users.data.0.name', 'Citra Dewi')
        ->where('users.data.1.name', 'Budi Santoso')
    );
});

test('admin can sort users by name asc and desc', function () {
    User::factory()->create([
        'role' => 'user',
        'name' => 'Zulfa Halim',
    ]);

    User::factory()->create([
        'role' => 'user',
        'name' => 'Amiruddin',
    ]);

    // ASC
    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'sort_by' => 'name',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('filters.sort_by', 'name')
        ->where('filters.sort_direction', 'asc')
        ->where('users.data', fn ($data) => collect($data)->pluck('name')->search('Amiruddin') < collect($data)->pluck('name')->search('Zulfa Halim')
        )
    );

    // DESC
    $responseDesc = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'sort_by' => 'name',
        'sort_direction' => 'desc',
    ]));

    $responseDesc->assertOk();
    $responseDesc->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('filters.sort_by', 'name')
        ->where('filters.sort_direction', 'desc')
        ->where('users.data', fn ($data) => collect($data)->pluck('name')->search('Zulfa Halim') < collect($data)->pluck('name')->search('Amiruddin')
        )
    );
});

test('admin can sort users by status asc and desc', function () {
    User::factory()->create([
        'role' => 'user',
        'name' => 'User Suspended',
        'status' => 'suspended',
    ]);

    User::factory()->create([
        'role' => 'user',
        'name' => 'User Active',
        'status' => 'active',
    ]);

    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'sort_by' => 'status',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('filters.sort_by', 'status')
        ->where('filters.sort_direction', 'asc')
        ->where('users.data.0.status', 'active')
    );

    $responseDesc = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'sort_by' => 'status',
        'sort_direction' => 'desc',
    ]));

    $responseDesc->assertOk();
    $responseDesc->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('filters.sort_by', 'status')
        ->where('filters.sort_direction', 'desc')
        ->where('users.data.0.status', 'suspended')
    );
});

test('invalid sort_by and sort_direction fall back to created_at desc for users', function () {
    $response = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'sort_by' => 'password; DROP TABLE users;',
        'sort_direction' => 'sideways',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('filters.sort_by', 'created_at')
        ->where('filters.sort_direction', 'desc')
    );
});

test('admin can customize users per page count', function () {
    for ($i = 1; $i <= 30; $i++) {
        User::factory()->create([
            'role' => 'user',
            'name' => "User {$i}",
        ]);
    }

    $response = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'per_page' => 25,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('users.per_page', 25)
        ->where('filters.per_page', 25)
    );

    // Fallback for invalid per_page
    $responseFallback = $this->actingAs($this->adminUser)->get(route('admin.users.index', [
        'per_page' => 9999,
    ]));

    $responseFallback->assertOk();
    $responseFallback->assertInertia(fn (Assert $page) => $page
        ->component('admin/users/Index')
        ->where('users.per_page', 15)
        ->where('filters.per_page', 15)
    );
});
