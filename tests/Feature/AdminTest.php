<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    $this->regularUser = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'user',
        'status' => 'active',
    ]);

    $this->adminUser = User::factory()->create([
        'email_verified_at' => now(),
        'role' => 'admin',
        'status' => 'active',
    ]);
});

test('unauthenticated user accessing admin dashboard is redirected to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('regular non-admin user accessing admin area gets 403 forbidden', function () {
    $response = $this->actingAs($this->regularUser)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('admin can access operational dashboard with consolidated metrics and recent audit logs', function () {
    $response = $this->actingAs($this->adminUser)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('admin/Dashboard')
        ->has('metrics.total_users')
        ->has('metrics.active_users')
        ->has('metrics.suspended_users')
        ->has('metrics.verified_users')
        ->has('metrics.unverified_users')
        ->has('metrics.new_registrations_30d')
        ->has('metrics.system_categories')
        ->has('metrics.income_categories')
        ->has('metrics.expense_categories')
        ->has('recentAuditLogs')
    );
});

test('admin can view user management list', function () {
    $response = $this->actingAs($this->adminUser)->get(route('admin.users.index'));

    $response->assertOk();
});

test('admin can suspend active user and audit log is recorded', function () {
    $targetUser = User::factory()->create([
        'email_verified_at' => now(),
        'status' => 'active',
    ]);

    $response = $this->actingAs($this->adminUser)->post(route('admin.users.suspension', $targetUser), [
        'reason' => 'Suspicious bulk activity',
    ]);

    $response->assertRedirect();

    $targetUser->refresh();
    expect($targetUser->status)->toBe('suspended');

    $this->assertDatabaseHas('admin_audit_logs', [
        'admin_user_id' => $this->adminUser->id,
        'action' => 'user.suspended',
        'target_type' => User::class,
        'target_id' => $targetUser->id,
    ]);
});

test('admin cannot suspend their own account', function () {
    $response = $this->actingAs($this->adminUser)->post(route('admin.users.suspension', $this->adminUser), [
        'reason' => 'Self test',
    ]);

    $response->assertSessionHasErrors('user');

    $this->adminUser->refresh();
    expect($this->adminUser->status)->toBe('active');
});

test('suspended user is terminated from accessing app routes', function () {
    $suspendedUser = User::factory()->create([
        'email_verified_at' => now(),
        'status' => 'suspended',
    ]);

    $response = $this->actingAs($suspendedUser)->get(route('app.dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

test('admin can view system categories and audit logs', function () {
    $responseCategories = $this->actingAs($this->adminUser)->get(route('admin.categories.index'));
    $responseCategories->assertOk();

    $responseLogs = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index'));
    $responseLogs->assertOk();
});
