<?php

use App\Models\User;

test('serves valid robots.txt with disallows for private areas and sitemap pointer', function () {
    $response = $this->get('/robots.txt');

    $response->assertStatus(200);
    $response->assertSee('User-agent: *');
    $response->assertSee('Disallow: /app/');
    $response->assertSee('Disallow: /admin/');
    $response->assertSee('Disallow: /email/');
    $response->assertSee('Disallow: /reset-password/');
    $response->assertSee('Sitemap: /sitemap.xml');
});

test('serves valid sitemap.xml with public routes and proper XML content type', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/xml');
    $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
    $response->assertSee(url('/'), false);
    $response->assertSee(url('/login'), false);
    $response->assertSee(url('/register'), false);
    $response->assertSee(url('/forgot-password'), false);

    // Private routes must NOT be in sitemap
    $response->assertDontSee('/app/');
    $response->assertDontSee('/admin/');
});

test('serves valid site.webmanifest JSON', function () {
    $response = $this->get('/site.webmanifest');

    $response->assertStatus(200);
    $data = json_decode($response->getContent(), true);

    expect($data)->toBeArray()
        ->and($data['name'])->toBe('CatatDuit - Pencatatan Keuangan Pribadi')
        ->and($data['short_name'])->toBe('CatatDuit')
        ->and($data['display'])->toBe('standalone')
        ->and($data['theme_color'])->toBe('#003A70');
});

test('renders core SEO meta tags, OpenGraph, Twitter Cards, and JSON-LD schema on public pages', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200);
    $response->assertSee('<meta name="description"', false);
    $response->assertSee('<meta name="keywords"', false);
    $response->assertSee('<meta name="robots"', false);
    $response->assertSee('<link rel="canonical"', false);
    $response->assertSee('<link rel="manifest" href="/site.webmanifest"', false);

    // Open Graph
    $response->assertSee('<meta property="og:type" content="website"', false);
    $response->assertSee('<meta property="og:site_name" content="CatatDuit"', false);
    $response->assertSee('<meta property="og:title"', false);
    $response->assertSee('<meta property="og:description"', false);
    $response->assertSee('<meta property="og:image"', false);

    // Twitter Card
    $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);
    $response->assertSee('<meta name="twitter:title"', false);
    $response->assertSee('<meta name="twitter:description"', false);

    // Structured Data JSON-LD
    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type": "WebApplication"', false);
    $response->assertSee('"name": "CatatDuit"', false);
    $response->assertSee('"applicationCategory": "FinanceApplication"', false);
});

test('has high-resolution 1200x630 og-image.png generated in public directory', function () {
    $path = public_path('og-image.png');

    expect(file_exists($path))->toBeTrue();
    [$width, $height] = getimagesize($path);
    expect($width)->toBe(1200)
        ->and($height)->toBe(630);
});

test('ensures private authenticated dashboard route responds successfully', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('app.dashboard'));

    $response->assertStatus(200);
});
