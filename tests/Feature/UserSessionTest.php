<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'password' => Hash::make('password123'),
        'status' => 'active',
    ]);
});

test('user can view security page with active sessions data', function () {
    $currentSessionId = Str::random(40);
    $otherSessionId = Str::random(40);

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => $otherSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            'payload' => '',
            'last_activity' => now()->subMinutes(30)->timestamp,
        ],
    ]);

    $response = $this->withCookie(config('session.cookie'), $currentSessionId)
        ->actingAs($this->user)
        ->get(route('profile.security'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('app/profile/Security')
        ->has('sessions', 2)
        ->where('sessions.0.is_current_device', true)
        ->where('sessions.0.agent.platform', 'Windows')
        ->where('sessions.0.agent.browser', 'Google Chrome')
        ->where('sessions.1.is_current_device', false)
        ->where('sessions.1.agent.platform', 'iPhone (iOS)')
        ->where('sessions.1.agent.device_type', 'mobile')
    );
});

test('legacy sessions page redirects to security page', function () {
    $response = $this->actingAs($this->user)->get(route('profile.sessions.index'));

    $response->assertRedirect(route('profile.security'));
});

test('user can revoke a specific device session', function () {
    $currentSessionId = 'session_current_123';
    $otherSessionId = 'session_other_456';
    $appKey = (string) config('app.key');

    session()->setId($currentSessionId);

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => $otherSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.5',
            'user_agent' => 'Mozilla/5.0 (Android 14; Mobile)',
            'payload' => '',
            'last_activity' => now()->subHours(1)->timestamp,
        ],
    ]);

    $hashedToken = hash('sha256', $otherSessionId.$appKey);

    $response = $this->actingAs($this->user)->delete(route('profile.sessions.destroy', $hashedToken));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(DB::table('sessions')->where('id', $otherSessionId)->exists())->toBeFalse();
    expect(DB::table('sessions')->where('id', $currentSessionId)->exists())->toBeTrue();
});

test('user cannot revoke current session using specific destroy endpoint', function () {
    $currentSessionId = Str::random(40);
    $appKey = (string) config('app.key');

    DB::table('sessions')->insert([
        'id' => $currentSessionId,
        'user_id' => $this->user->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $hashedToken = hash('sha256', $currentSessionId.$appKey);

    $cookieName = config('session.cookie');

    $response = $this->withCookie($cookieName, $currentSessionId)
        ->actingAs($this->user)
        ->delete(route('profile.sessions.destroy', $hashedToken));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(DB::table('sessions')->where('id', $currentSessionId)->exists())->toBeTrue();
});

test('user cannot revoke session belonging to another user', function () {
    $otherUser = User::factory()->create([
        'email_verified_at' => now(),
        'status' => 'active',
    ]);

    $victimSessionId = 'victim_session_id';
    $appKey = (string) config('app.key');

    DB::table('sessions')->insert([
        'id' => $victimSessionId,
        'user_id' => $otherUser->id,
        'ip_address' => '10.0.0.99',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $hashedToken = hash('sha256', $victimSessionId.$appKey);

    $response = $this->actingAs($this->user)->delete(route('profile.sessions.destroy', $hashedToken));

    $response->assertRedirect();
    $response->assertSessionHas('error');

    expect(DB::table('sessions')->where('id', $victimSessionId)->exists())->toBeTrue();
});

test('user can revoke all other sessions with valid password', function () {
    $currentSessionId = Str::random(40);
    $other1 = Str::random(40);
    $other2 = Str::random(40);

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => $other1,
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone)',
            'payload' => '',
            'last_activity' => now()->subMinutes(15)->timestamp,
        ],
        [
            'id' => $other2,
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.2',
            'user_agent' => 'Mozilla/5.0 (Android)',
            'payload' => '',
            'last_activity' => now()->subMinutes(45)->timestamp,
        ],
    ]);

    $response = $this->withCookie(config('session.cookie'), $currentSessionId)
        ->actingAs($this->user)
        ->post(route('profile.sessions.destroy-others'), [
            'password' => 'password123',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(DB::table('sessions')->where('id', $currentSessionId)->exists())->toBeTrue();
    expect(DB::table('sessions')->where('id', $other1)->exists())->toBeFalse();
    expect(DB::table('sessions')->where('id', $other2)->exists())->toBeFalse();
});

test('user cannot revoke other sessions with invalid password', function () {
    $currentSessionId = 'session_stay_alive_2';
    $otherSession = 'session_still_alive';

    session()->setId($currentSessionId);

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => $otherSession,
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone)',
            'payload' => '',
            'last_activity' => now()->subMinutes(10)->timestamp,
        ],
    ]);

    $response = $this->actingAs($this->user)->post(route('profile.sessions.destroy-others'), [
        'password' => 'wrong_password',
    ]);

    $response->assertSessionHasErrors('password');
    expect(DB::table('sessions')->where('id', $otherSession)->exists())->toBeTrue();
});

test('user can logout from all devices with valid password', function () {
    $currentSessionId = 'session_global_1';
    $otherSession = 'session_global_2';

    session()->setId($currentSessionId);

    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $this->user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0)',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => $otherSession,
            'user_id' => $this->user->id,
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone)',
            'payload' => '',
            'last_activity' => now()->subMinutes(10)->timestamp,
        ],
    ]);

    $response = $this->actingAs($this->user)->post(route('profile.sessions.destroy-all'), [
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success');

    expect(DB::table('sessions')->where('user_id', $this->user->id)->count())->toBe(0);
    $this->assertGuest();
});
