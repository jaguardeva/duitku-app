<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'CatatDuit') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body, table, td, p, a, h1, h2, h3, span {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        }
    </style>
</head>
<body style="margin: 0; padding: 24px 12px; background-color: #f8fafc; color: #1e293b; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0; mso-hide: all; font-size: 1px; line-height: 1px;">
        {{ $preheader ?? '' }}
    </div>

    <!-- Main Email Container (Flat, Simple, Spacious) -->
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0;">
        <!-- Header Top Stripe (Gold) -->
        <tr style="line-height: 0; font-size: 0;">
            <td style="height: 4px; background-color: #FFB700; line-height: 4px; font-size: 0;" bgcolor="#FFB700">&nbsp;</td>
        </tr>

        <!-- Header Block (Blue Navy, Logo, Crisp White & Gold Text) -->
        <tr>
            <td align="center" style="padding: 28px 32px 22px; background-color: #003a70; text-align: center;" bgcolor="#003a70">
                <!-- Brand Logo (Clean, No Box-in-Box) -->
                <img src="{{ url('/favicon.png') }}" alt="{{ config('app.name', 'CatatDuit') }}" width="48" height="48" style="display: block; margin: 0 auto 10px; width: 48px; height: 48px; object-fit: contain;">

                <!-- App Name in Crisp White -->
                <h1 style="margin: 0; font-size: 20px; font-weight: 700; letter-spacing: -0.3px; color: #ffffff; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    {{ config('app.name', 'CatatDuit') }}
                </h1>

                <!-- Subtitle in Gold -->
                <p style="margin: 3px 0 0; font-size: 11px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; color: #FFD200; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    Pencatatan Keuangan Pribadi
                </p>
            </td>
        </tr>

        <!-- Main Body Content (Spacious, Full-Width Flow, No Nested Cards) -->
        <tr>
            <td style="padding: 36px 36px 40px; background-color: #ffffff; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" bgcolor="#ffffff">
                @yield('content')
            </td>
        </tr>

        <!-- Footer Top Separator (Gold Stripe) -->
        <tr style="line-height: 0; font-size: 0;">
            <td style="height: 3px; background-color: #FFB700; line-height: 3px; font-size: 0;" bgcolor="#FFB700">&nbsp;</td>
        </tr>

        <!-- Footer Block (Blue Navy Background, Flat) -->
        <tr>
            <td align="center" style="padding: 26px 32px 24px; background-color: #003a70; text-align: center; color: #c5d9f0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" bgcolor="#003a70">
                <!-- App Title -->
                <div style="font-size: 14px; font-weight: 700; color: #ffffff; margin-bottom: 8px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    {{ config('app.name', 'CatatDuit') }}
                </div>

                <!-- No-Reply Security Notice -->
                <p style="margin: 0 0 14px; font-size: 12px; line-height: 1.6; color: #c5d9f0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    Email ini dikirim secara otomatis oleh sistem untuk keamanan akun Anda. Mohon untuk tidak membalas email ini.
                </p>

                <!-- Visit App Link in Gold -->
                <div style="margin-bottom: 16px;">
                    <a href="{{ config('app.url') }}" style="display: inline-block; font-size: 12px; font-weight: 600; color: #FFD200; text-decoration: none; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                        Kunjungi {{ config('app.name', 'CatatDuit') }} &rarr;
                    </a>
                </div>

                <!-- Copyright Notice -->
                <div style="font-size: 11px; color: #94b4d8; border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 14px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                    &copy; {{ date('Y') }} {{ config('app.name', 'CatatDuit') }}. Hak cipta dilindungi.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
