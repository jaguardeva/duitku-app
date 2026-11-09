<?php

use App\Models\AdminAuditLog;
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

test('unauthenticated user cannot access admin audit logs list', function () {
    $response = $this->get(route('admin.audit-logs.index'));

    $response->assertRedirect(route('login'));
});

test('regular user cannot access admin audit logs list', function () {
    $response = $this->actingAs($this->regularUser)->get(route('admin.audit-logs.index'));

    $response->assertForbidden();
});

test('admin can view audit logs list with pagination meta and default sorting', function () {
    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'first.action',
        'ip_address' => '10.0.0.1',
        'created_at' => now()->subMinutes(10),
    ]);

    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'second.action',
        'ip_address' => '10.0.0.2',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->has('auditLogs.data')
        ->where('auditLogs.per_page', 20)
        ->where('filters.sort_by', 'created_at')
        ->where('filters.sort_direction', 'desc')
        ->where('auditLogs.data.0.action', 'second.action')
    );
});

test('admin can sort audit logs by action asc and desc', function () {
    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'zebra.action',
        'ip_address' => '10.0.0.1',
        'created_at' => now()->subMinutes(5),
    ]);

    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'alpha.action',
        'ip_address' => '10.0.0.2',
        'created_at' => now()->subMinutes(4),
    ]);

    // ASC
    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'sort_by' => 'action',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('filters.sort_by', 'action')
        ->where('filters.sort_direction', 'asc')
        ->where('auditLogs.data.0.action', 'alpha.action')
        ->where('auditLogs.data.1.action', 'zebra.action')
    );

    // DESC
    $responseDesc = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'sort_by' => 'action',
        'sort_direction' => 'desc',
    ]));

    $responseDesc->assertOk();
    $responseDesc->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('filters.sort_by', 'action')
        ->where('filters.sort_direction', 'desc')
        ->where('auditLogs.data.0.action', 'zebra.action')
        ->where('auditLogs.data.1.action', 'alpha.action')
    );
});

test('admin can sort audit logs by ip_address asc and desc', function () {
    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'user.suspended',
        'ip_address' => '192.168.1.1',
        'created_at' => now()->subMinutes(2),
    ]);

    AdminAuditLog::create([
        'admin_user_id' => $this->adminUser->id,
        'action' => 'user.activated',
        'ip_address' => '10.0.0.1',
        'created_at' => now()->subMinute(),
    ]);

    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'sort_by' => 'ip_address',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('filters.sort_by', 'ip_address')
        ->where('filters.sort_direction', 'asc')
        ->where('auditLogs.data.0.ip_address', '10.0.0.1')
        ->where('auditLogs.data.1.ip_address', '192.168.1.1')
    );
});

test('invalid sort_by and sort_direction fall back to created_at desc for audit logs', function () {
    $response = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'sort_by' => 'unknown_col; select * from users;',
        'sort_direction' => 'upwards',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('filters.sort_by', 'created_at')
        ->where('filters.sort_direction', 'desc')
    );
});

test('admin can customize audit logs per page count', function () {
    for ($i = 1; $i <= 35; $i++) {
        AdminAuditLog::create([
            'admin_user_id' => $this->adminUser->id,
            'action' => "action.{$i}",
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subSeconds($i),
        ]);
    }

    $response = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'per_page' => 50,
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('auditLogs.per_page', 50)
        ->where('filters.per_page', 50)
    );

    // Fallback for invalid per_page
    $responseFallback = $this->actingAs($this->adminUser)->get(route('admin.audit-logs.index', [
        'per_page' => 1234,
    ]));

    $responseFallback->assertOk();
    $responseFallback->assertInertia(fn (Assert $page) => $page
        ->component('admin/audit-logs/Index')
        ->where('auditLogs.per_page', 20)
        ->where('filters.per_page', 20)
    );
});
