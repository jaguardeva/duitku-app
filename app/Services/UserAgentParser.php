<?php

namespace App\Services;

class UserAgentParser
{
    /**
     * Parse raw user agent into structured information.
     *
     * @return array{
     *     device_type: 'desktop'|'mobile'|'tablet'|'bot',
     *     platform: string,
     *     browser: string,
     *     display_name: string
     * }
     */
    public static function parse(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'device_type' => 'desktop',
                'platform' => 'Tidak Diketahui',
                'browser' => 'Peramban Web',
                'display_name' => 'Peramban Web',
            ];
        }

        $platform = self::parsePlatform($userAgent);
        $browser = self::parseBrowser($userAgent);
        $deviceType = self::parseDeviceType($userAgent, $platform);

        $displayName = "{$browser} di {$platform}";

        return [
            'device_type' => $deviceType,
            'platform' => $platform,
            'browser' => $browser,
            'display_name' => $displayName,
        ];
    }

    /**
     * Detect operating system or platform.
     */
    protected static function parsePlatform(string $userAgent): string
    {
        $platforms = [
            'Windows' => '/windows nt 10\.0/i',
            'Windows 11/10' => '/windows nt 10/i',
            'Windows 8.1' => '/windows nt 6\.3/i',
            'Windows 8' => '/windows nt 6\.2/i',
            'Windows 7' => '/windows nt 6\.1/i',
            'Windows' => '/windows nt|win32|win64/i',
            'iPhone (iOS)' => '/iphone/i',
            'iPad (iPadOS)' => '/ipad/i',
            'iOS' => '/ipod/i',
            'Android' => '/android/i',
            'macOS' => '/macintosh|mac os x/i',
            'ChromeOS' => '/cros/i',
            'Linux' => '/linux/i',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return $platform;
            }
        }

        return 'Sistem Tidak Dikenal';
    }

    /**
     * Detect web browser.
     */
    protected static function parseBrowser(string $userAgent): string
    {
        if (preg_match('/edg(?:e|a|ios)?\/([0-9.]+)/i', $userAgent)) {
            return 'Microsoft Edge';
        }

        if (preg_match('/opr\/|opera/i', $userAgent)) {
            return 'Opera';
        }

        if (preg_match('/brave/i', $userAgent)) {
            return 'Brave';
        }

        if (preg_match('/vivaldi/i', $userAgent)) {
            return 'Vivaldi';
        }

        if (preg_match('/samsungbrowser/i', $userAgent)) {
            return 'Samsung Internet';
        }

        if (preg_match('/chrome|crios/i', $userAgent)) {
            return 'Google Chrome';
        }

        if (preg_match('/firefox|fxios/i', $userAgent)) {
            return 'Mozilla Firefox';
        }

        if (preg_match('/safari/i', $userAgent) && ! preg_match('/chrome|crios|android/i', $userAgent)) {
            return 'Apple Safari';
        }

        return 'Peramban Web';
    }

    /**
     * Detect device type form factor.
     *
     * @return 'desktop'|'mobile'|'tablet'|'bot'
     */
    protected static function parseDeviceType(string $userAgent, string $platform): string
    {
        if (preg_match('/bot|crawl|slurp|spider|mediapartners/i', $userAgent)) {
            return 'bot';
        }

        if (preg_match('/ipad|tablet|playbook|silk/i', $userAgent) || ($platform === 'Android' && ! preg_match('/mobile/i', $userAgent))) {
            return 'tablet';
        }

        if (preg_match('/mobile|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent) || ($platform === 'Android' && preg_match('/mobile/i', $userAgent))) {
            return 'mobile';
        }

        return 'desktop';
    }
}
