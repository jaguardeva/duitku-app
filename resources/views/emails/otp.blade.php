@extends('emails.layout', [
    'title' => 'Kode verifikasi email '.config('app.name', 'CatatDuit'),
    'preheader' => 'Kode verifikasi akun Anda di '.config('app.name', 'CatatDuit').'.',
])

@section('content')
    <!-- Main Title -->
    <h2 style="margin: 0 0 14px; font-size: 20px; font-weight: 700; line-height: 1.4; color: #003a70; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Konfirmasi Alamat Email Anda
    </h2>

    <!-- Body Explanation -->
    <p style="margin: 0 0 24px; font-size: 14px; line-height: 1.7; color: #475569; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Gunakan kode verifikasi berikut untuk menyelesaikan pendaftaran akun <strong>{{ config('app.name', 'CatatDuit') }}</strong> Anda. Kode ini berlaku selama {{ $expiryMinutes }} menit.
    </p>

    <!-- OTP Code Display (Clean, Spacious, No Nested Boxes) -->
    <div style="margin: 28px 0; text-align: center; padding: 20px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
        <span style="display: block; font-size: 11px; font-weight: 700; letter-spacing: 2px; color: #64748b; margin-bottom: 8px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            KODE OTP
        </span>
        <span style="display: block; font-size: 36px; font-weight: 800; line-height: 1.1; letter-spacing: 8px; color: #003a70; font-family: 'Courier New', Courier, monospace;">
            {{ $otpCode }}
        </span>
        <span style="display: block; margin-top: 8px; font-size: 12px; color: #64748b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            Berlaku selama {{ $expiryMinutes }} menit
        </span>
    </div>

    <!-- Security Warning (Clean Text, No Cluttered Cards) -->
    <p style="margin: 24px 0 0; font-size: 12px; line-height: 1.65; color: #64748b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Demi keamanan, jangan bagikan kode ini kepada siapa pun termasuk pihak yang mengatasnamakan {{ config('app.name', 'CatatDuit') }}. Jika Anda tidak merasa meminta kode ini, abaikan email ini dan akun Anda akan tetap aman.
    </p>
@endsection
