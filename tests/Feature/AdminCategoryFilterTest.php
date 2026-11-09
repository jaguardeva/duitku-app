<?php

use App\Models\Category;
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

test('unauthenticated user cannot access admin categories', function () {
    $response = $this->get(route('admin.categories.index'));

    $response->assertRedirect(route('login'));
});

test('regular user cannot access admin categories', function () {
    $response = $this->actingAs($this->regularUser)->get(route('admin.categories.index'));

    $response->assertForbidden();
});

test('admin can view all system categories with pagination meta', function () {
    Category::factory()->system()->create([
        'name' => 'Transportasi Rutin',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    Category::factory()->system()->create([
        'name' => 'Gaji Pokok',
        'type' => 'income',
        'is_archived' => false,
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.categories.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->has('categories.data')
        ->where('categories.per_page', 10)
        ->has('filters')
    );
});

test('admin can filter categories by search keyword', function () {
    Category::factory()->system()->create([
        'name' => 'Langganan Internet',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    Category::factory()->system()->create([
        'name' => 'Tiket Bioskop',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'search' => 'Internet',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.search', 'Internet')
        ->where('categories.data', fn ($data) => collect($data)->contains('name', 'Langganan Internet')
            && ! collect($data)->contains('name', 'Tiket Bioskop')
        )
    );
});

test('admin can filter categories by type', function () {
    Category::factory()->system()->create([
        'name' => 'Uang Lembur',
        'type' => 'income',
        'is_archived' => false,
    ]);

    Category::factory()->system()->create([
        'name' => 'Makan Siang',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    // Test expense filter
    $responseExpense = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'type' => 'expense',
    ]));

    $responseExpense->assertOk();
    $responseExpense->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.type', 'expense')
        ->where('categories.data', fn ($data) => collect($data)->every(fn ($cat) => $cat['type'] === 'expense')
            && collect($data)->contains('name', 'Makan Siang')
            && ! collect($data)->contains('name', 'Uang Lembur')
        )
    );

    // Test income filter
    $responseIncome = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'type' => 'income',
    ]));

    $responseIncome->assertOk();
    $responseIncome->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.type', 'income')
        ->where('categories.data', fn ($data) => collect($data)->every(fn ($cat) => $cat['type'] === 'income')
            && collect($data)->contains('name', 'Uang Lembur')
            && ! collect($data)->contains('name', 'Makan Siang')
        )
    );
});

test('admin can filter categories by status', function () {
    Category::factory()->system()->create([
        'name' => 'Kategori Lama',
        'type' => 'expense',
        'is_archived' => true,
    ]);

    Category::factory()->system()->create([
        'name' => 'Kategori Aktif',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    // Test active filter
    $responseActive = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'status' => 'active',
    ]));

    $responseActive->assertOk();
    $responseActive->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.status', 'active')
        ->where('categories.data', fn ($data) => collect($data)->every(fn ($cat) => ! $cat['is_archived'])
            && collect($data)->contains('name', 'Kategori Aktif')
            && ! collect($data)->contains('name', 'Kategori Lama')
        )
    );

    // Test archived filter
    $responseArchived = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'status' => 'archived',
    ]));

    $responseArchived->assertOk();
    $responseArchived->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.status', 'archived')
        ->where('categories.data', fn ($data) => collect($data)->every(fn ($cat) => (bool) $cat['is_archived'])
            && collect($data)->contains('name', 'Kategori Lama')
            && ! collect($data)->contains('name', 'Kategori Aktif')
        )
    );
});

test('admin can combine search, type, and status filters', function () {
    Category::factory()->system()->create([
        'name' => 'Target Pengeluaran Aktif',
        'type' => 'expense',
        'is_archived' => false,
    ]);

    Category::factory()->system()->create([
        'name' => 'Target Pengeluaran Arsip',
        'type' => 'expense',
        'is_archived' => true,
    ]);

    Category::factory()->system()->create([
        'name' => 'Target Pemasukan Aktif',
        'type' => 'income',
        'is_archived' => false,
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'search' => 'Target',
        'type' => 'expense',
        'status' => 'active',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.search', 'Target')
        ->where('filters.type', 'expense')
        ->where('filters.status', 'active')
        ->where('categories.data', fn ($data) => collect($data)->count() === 1
            && collect($data)->first()['name'] === 'Target Pengeluaran Aktif'
        )
    );
});

test('user personal categories are excluded from admin system categories list', function () {
    Category::factory()->create([
        'user_id' => $this->regularUser->id,
        'name' => 'Kategori Rahasia User',
        'type' => 'expense',
        'is_system' => false,
    ]);

    $response = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'search' => 'Rahasia',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('categories.data', fn ($data) => ! collect($data)->contains('name', 'Kategori Rahasia User'))
    );
});

test('admin can customize categories per page count', function () {
    // Create 15 categories
    for ($i = 1; $i <= 15; $i++) {
        Category::factory()->system()->create([
            'name' => "Kategori Custom {$i}",
            'type' => 'expense',
        ]);
    }

    $response25 = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'per_page' => 25,
    ]));

    $response25->assertOk();
    $response25->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('categories.per_page', 25)
        ->where('filters.per_page', 25)
    );

    // Invalid per_page falls back to default 10
    $responseInvalid = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'per_page' => 999,
    ]));

    $responseInvalid->assertOk();
    $responseInvalid->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('categories.per_page', 10)
        ->where('filters.per_page', 10)
    );
});

test('admin can sort categories by name asc and desc', function () {
    Category::factory()->system()->create([
        'name' => 'Zara Clothing',
        'type' => 'expense',
    ]);
    Category::factory()->system()->create([
        'name' => 'Apple Music',
        'type' => 'expense',
    ]);

    // ASC
    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'sort_by' => 'name',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.sort_by', 'name')
        ->where('filters.sort_direction', 'asc')
        ->where('categories.data.0.name', 'Apple Music')
        ->where('categories.data.1.name', 'Zara Clothing')
    );

    // DESC
    $responseDesc = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'sort_by' => 'name',
        'sort_direction' => 'desc',
    ]));

    $responseDesc->assertOk();
    $responseDesc->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.sort_by', 'name')
        ->where('filters.sort_direction', 'desc')
        ->where('categories.data.0.name', 'Zara Clothing')
        ->where('categories.data.1.name', 'Apple Music')
    );
});

test('admin can sort categories by sort_order asc and desc', function () {
    Category::factory()->system()->create([
        'name' => 'Kategori Urutan Dua',
        'sort_order' => 20,
    ]);
    Category::factory()->system()->create([
        'name' => 'Kategori Urutan Satu',
        'sort_order' => 10,
    ]);

    $responseAsc = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'sort_by' => 'sort_order',
        'sort_direction' => 'asc',
    ]));

    $responseAsc->assertOk();
    $responseAsc->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.sort_by', 'sort_order')
        ->where('filters.sort_direction', 'asc')
        ->where('categories.data.0.sort_order', 10)
        ->where('categories.data.1.sort_order', 20)
    );

    $responseDesc = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'sort_by' => 'sort_order',
        'sort_direction' => 'desc',
    ]));

    $responseDesc->assertOk();
    $responseDesc->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.sort_by', 'sort_order')
        ->where('filters.sort_direction', 'desc')
        ->where('categories.data.0.sort_order', 20)
        ->where('categories.data.1.sort_order', 10)
    );
});

test('invalid sort_by and sort_direction fall back to safe defaults', function () {
    $response = $this->actingAs($this->adminUser)->get(route('admin.categories.index', [
        'sort_by' => 'malicious_column; DROP TABLE categories;',
        'sort_direction' => 'diagonal',
    ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/categories/Index')
        ->where('filters.sort_by', 'sort_order')
        ->where('filters.sort_direction', 'asc')
    );
});
