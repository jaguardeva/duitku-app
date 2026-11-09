@extends('emails.layout', [
    'title' => 'Atur ulang password '.config('app.name', 'CatatDuit'),
    'preheader' => 'Permintaan untuk mengatur ulang kata sandi akun Anda di '.config('app.name', 'CatatDuit').'.',
])

@section('content')
    <!-- Main Title -->
    <h2 style="margin: 0 0 14px; font-size: 20px; font-weight: 700; line-height: 1.4; color: #003a70; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Atur Ulang Password Anda
    </h2>

    <!-- Body Explanation -->
    <p style="margin: 0 0 26px; font-size: 14px; line-height: 1.7; color: #475569; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Kami menerima permintaan untuk mengatur ulang kata sandi akun <strong>{{ config('app.name', 'CatatDuit') }}</strong> Anda. Silakan klik tombol di bawah ini untuk membuat kata sandi baru:
    </p>

    <!-- Action Button (Flat, Clean, Inter Font) -->
    <div style="margin: 28px 0; text-align: center;">
        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 13px 32px; background-color: #003a70; color: #ffffff; font-size: 13px; font-weight: 700; text-decoration: none; border-bottom: 3px solid #FFB700; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            Atur Ulang Password
        </a>
    </div>

    <!-- Fallback Link (Clean Text, No Cluttered Nested Card) -->
    <p style="margin: 26px 0 0; font-size: 12px; line-height: 1.6; color: #64748b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut ke browser Anda:<br>
        <a href="{{ $resetUrl }}" style="color: #003a70; word-break: break-all; text-decoration: underline; font-family: 'Courier New', Courier, monospace;">{{ $resetUrl }}</a>
    </p>

    <!-- Security Warning (Clean Separator Line) -->
    <p style="margin: 22px 0 0; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; line-height: 1.65; color: #64748b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
        Tautan ini hanya berlaku untuk waktu terbatas demi keamanan akun Anda. Jika Anda tidak meminta perubahan kata sandi, akun Anda tetap aman dan Anda dapat mengabaikan email ini.
    </p>
@endsection
