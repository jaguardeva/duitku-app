<?php

use App\Models\AdminAuditLog;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

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

test('app_version is shared in inertia props with default value', function () {
    $response = $this->actingAs($this->regularUser)->get(route('app.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('app_version')
        ->where('app_version', '1.0.0')
    );
});

test('guest cannot update app version and is redirected to login', function () {
    $response = $this->put(route('admin.version.update'), [
        'version' => '1.5.0',
    ]);

    $response->assertRedirect(route('login'));
});

test('regular user cannot update app version and receives 403 forbidden', function () {
    $response = $this->actingAs($this->regularUser)->put(route('admin.version.update'), [
        'version' => '1.5.0',
    ]);

    $response->assertForbidden();
});

test('admin can update app version successfully and audit log is recorded', function () {
    $response = $this->actingAs($this->adminUser)->put(route('admin.version.update'), [
        'version' => '1.2.0',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', __('messages.admin.version_updated'));

    expect(AppSetting::get('app_version'))->toBe('1.2.0');

    $this->assertDatabaseHas('admin_audit_logs', [
        'admin_user_id' => $this->adminUser->id,
        'action' => 'system.version_update',
        'target_type' => 'system',
    ]);

    $log = AdminAuditLog::query()
        ->where('admin_user_id', $this->adminUser->id)
        ->where('action', 'system.version_update')
        ->latest('created_at')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->metadata['new_version'])->toBe('1.2.0');
    expect($log->metadata['old_version'])->toBe('1.0.0');
});

test('admin receives validation errors for invalid version format', function (string $invalidVersion) {
    $response = $this->actingAs($this->adminUser)->put(route('admin.version.update'), [
        'version' => $invalidVersion,
    ]);

    $response->assertSessionHasErrors('version');
})->with([
    '',
    'invalid_version',
    '1.0.',
    '.1.0',
    'version 1 0',
    '1.0.0; DROP TABLE users;',
]);

test('updated app version is immediately reflected in inertia shared props', function () {
    AppSetting::set('app_version', '2.5.1');

    $response = $this->actingAs($this->regularUser)->get(route('app.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('app_version', '2.5.1')
    );
});
