<?php

use App\Mail\SendOtpMail;
use App\Mail\SendPasswordResetMail;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register with valid password and receive OTP', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi@example.com',
        'password' => 'Budi#Santoso2026!',
        'password_confirmation' => 'Budi#Santoso2026!',
        'timezone' => 'Asia/Jakarta',
    ]);

    $this->assertAuthenticated();
    $user = User::where('email', 'budi@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe('user');
    expect($user->status)->toBe('active');
    expect($user->email_verified_at)->toBeNull();
    expect($user->timezone)->toBe('Asia/Jakarta');

    Mail::assertSent(SendOtpMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    $response->assertRedirect('/app');
});

test('registration rejects password with whitespace', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi2@example.com',
        'password' => 'Budi #Santoso2026!',
        'password_confirmation' => 'Budi #Santoso2026!',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
});

test('registration rejects password with fewer than 8 characters', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi3@example.com',
        'password' => 'Sho#1!',
        'password_confirmation' => 'Sho#1!',
    ]);

    $response->assertSessionHasErrors('password');
});

test('registration accepts valid 8 characters password', function () {
    $response = $this->post('/register', [
        'name' => 'Budi Santoso',
        'email' => 'budi8@example.com',
        'password' => 'Budi#12!',
        'password_confirmation' => 'Budi#12!',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertAuthenticated();
});

test('user can verify email with correct OTP code', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create([
        'email' => 'verifyme@example.com',
    ]);

    $otpService = app(OtpService::class);
    $otp = $otpService->sendOtp($user);

    // Grab the generated code from the sent email
    $sentCode = null;
    Mail::assertSent(SendOtpMail::class, function ($mail) use (&$sentCode) {
        $sentCode = $mail->otpCode;

        return true;
    });

    expect($sentCode)->not->toBeNull();

    $response = $this->actingAs($user)->post('/email/verify-otp', [
        'code' => $sentCode,
    ]);

    $response->assertRedirect('/app');
    expect($user->fresh()->isEmailVerified())->toBeTrue();
});

test('otp verification page exposes a stable absolute resend cooldown timestamp', function () {
    $user = User::factory()->unverified()->create();
    $sentAt = now()->subSeconds(20);

    EmailVerificationOtp::create([
        'user_id' => $user->id,
        'code_hash' => hash('sha256', '123456'),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
        'max_attempts' => 5,
        'sent_at' => $sentAt,
    ]);

    $response = $this->actingAs($user)->get('/email/verify-otp');

    $response->assertInertia(fn ($page) => $page
        ->component('auth/VerifyOtp')
        ->where('cooldownUntil', $sentAt->copy()->addSeconds(OtpService::RESEND_COOLDOWN_SECONDS)->timestamp)
    );
});

test('unverified user can resend otp after the cooldown expires', function () {
    Mail::fake();

    $user = User::factory()->unverified()->create();

    EmailVerificationOtp::create([
        'user_id' => $user->id,
        'code_hash' => hash('sha256', '123456'),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
        'max_attempts' => 5,
        'sent_at' => now()->subSeconds(OtpService::RESEND_COOLDOWN_SECONDS + 1),
    ]);

    $response = $this->actingAs($user)->post('/email/verification-notification');

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(EmailVerificationOtp::query()->where('user_id', $user->id)->count())->toBe(2);
    Mail::assertSent(SendOtpMail::class);
});

test('otp verification fails with wrong code and records attempt', function () {
    $user = User::factory()->unverified()->create();

    $otp = EmailVerificationOtp::create([
        'user_id' => $user->id,
        'code_hash' => hash('sha256', '123456'),
        'expires_at' => Carbon::now('UTC')->addMinutes(10),
        'attempts' => 0,
        'max_attempts' => 5,
        'sent_at' => Carbon::now('UTC'),
    ]);

    $response = $this->actingAs($user)->post('/email/verify-otp', [
        'code' => '999999',
    ]);

    $response->assertSessionHasErrors('code');
    expect($otp->fresh()->attempts)->toBe(1);
    expect($user->fresh()->isEmailVerified())->toBeFalse();
});

test('expired otp cannot be verified', function () {
    $user = User::factory()->unverified()->create();

    $otp = EmailVerificationOtp::create([
        'user_id' => $user->id,
        'code_hash' => hash('sha256', '123456'),
        'expires_at' => Carbon::now('UTC')->subMinute(),
        'attempts' => 0,
        'max_attempts' => 5,
        'sent_at' => Carbon::now('UTC')->subMinutes(11),
    ]);

    $response = $this->actingAs($user)->post('/email/verify-otp', [
        'code' => '123456',
    ]);

    $response->assertSessionHasErrors('code');
    expect($user->fresh()->isEmailVerified())->toBeFalse();
});

test('suspended user cannot log in', function () {
    $user = User::factory()->suspended()->create([
        'email' => 'suspended@example.com',
        'password' => Hash::make('Password#12345'),
    ]);

    $response = $this->post('/login', [
        'email' => 'suspended@example.com',
        'password' => 'Password#12345',
    ]);

    $this->assertGuest();
});

test('inertia pages share dynamic app name from config', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('name', config('app.name'))
        ->where('appName', config('app.name'))
    );
});

test('otp mail envelope and template use dynamic app name', function () {
    $mailable = new SendOtpMail('654321', 10);
    $mailable->assertHasSubject('Kode Verifikasi Akun '.config('app.name'));
    $mailable->assertSeeInHtml(config('app.name'));
    $mailable->assertSeeInHtml('KODE OTP');
    $mailable->assertSeeInHtml('/favicon.png');
    $mailable->assertSeeInHtml('#003a70');
    $mailable->assertSeeInHtml('#FFB700');
    $mailable->assertSeeInHtml('Inter');
    $mailable->assertSeeInHtml('Hak cipta dilindungi');
    $mailable->assertSeeInText('654321');
});

test('forgot password screen can be rendered and passes status prop', function () {
    $response = $this->withSession(['status' => 'Tautan terkirim'])->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('auth/ForgotPassword')
        ->where('status', 'Tautan terkirim')
    );
});

test('password reset link can be requested by user and sets session cooldown', function () {
    Mail::fake();

    $user = User::factory()->create([
        'email' => 'resetuser@example.com',
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'resetuser@example.com',
    ]);

    $response->assertSessionHas('status', __('passwords.sent'));
    $response->assertSessionHas('password_reset_sent_at');
    $response->assertSessionHas('password_reset_email', 'resetuser@example.com');
    Mail::assertSent(SendPasswordResetMail::class, function ($mail) use ($user) {
        return $mail->hasTo($user->email);
    });
});

test('password reset mail uses the custom shared email design', function () {
    $mailable = new SendPasswordResetMail('https://catatduit.test/reset/example');

    $mailable->assertHasSubject('Atur ulang password '.config('app.name'));
    $mailable->assertSeeInHtml(config('app.name'));
    $mailable->assertSeeInHtml('/favicon.png');
    $mailable->assertSeeInHtml('#003a70');
    $mailable->assertSeeInHtml('#FFB700');
    $mailable->assertSeeInHtml('Inter');
    $mailable->assertSeeInHtml('https://catatduit.test/reset/example');
    $mailable->assertSeeInHtml('Hak cipta dilindungi');
    $mailable->assertSeeInText('https://catatduit.test/reset/example');
});

test('forgot password screen calculates cooldownSeconds on refresh from session', function () {
    $response = $this->withSession([
        'password_reset_sent_at' => now()->subSeconds(20)->timestamp,
        'password_reset_email' => 'persisted@example.com',
    ])->get('/forgot-password');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('auth/ForgotPassword')
        ->where('cooldownSeconds', fn ($cooldown) => $cooldown >= 38 && $cooldown <= 40)
        ->where('submittedEmail', 'persisted@example.com')
    );
});

test('subsequent password reset request within 60s is throttled with remaining seconds', function () {
    $user = User::factory()->create([
        'email' => 'throttleuser@example.com',
    ]);

    // First request
    $this->post('/forgot-password', [
        'email' => 'throttleuser@example.com',
    ]);

    // Immediate second request
    $secondResponse = $this->post('/forgot-password', [
        'email' => 'throttleuser@example.com',
    ]);

    $secondResponse->assertSessionHasErrors('email');
    $errors = session('errors')->get('email');
    expect($errors[0])->toMatch('/(?:59|60) seconds before requesting a new link/');
});
