<?php

declare(strict_types=1);

namespace App\Security;

use Nette;

class SecurityHeaders
{
    /**
     * Aplikuje bezpečnostní hlavičky na HTTP odpověď
     */
    public static function apply(Nette\Http\Response $httpResponse): void
    {
        // 🔒 HTTPS Enforcement (jen v produkci)
        self::enforceHttpsInProduction();

        // Zakáže MIME-sniffing v prohlížečích
        $httpResponse->setHeader('X-Content-Type-Options', 'nosniff');

        // Chrání před clickjacking útoky
        $httpResponse->setHeader('X-Frame-Options', 'DENY');

        // Nastavuje politiku referrerů
        $httpResponse->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Chrání před XSS útoky
        $httpResponse->setHeader('X-XSS-Protection', '1; mode=block');

        // Content Security Policy
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'",
            "style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-src 'none'",
            "object-src 'none'",
            "base-uri 'self'"
        ];

        $httpResponse->setHeader('Content-Security-Policy', implode('; ', $cspDirectives));

        // 🔒 HSTS header (jen pokud už je HTTPS)
        if (self::isHttpsRequest()) {
            $httpResponse->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // 🔒 NOVÉ: Pokročilé security headers
        $httpResponse->setHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=(), magnetometer=(), gyroscope=(), speaker=(), vibrate=(), fullscreen=(self)');
        $httpResponse->setHeader('Cross-Origin-Embedder-Policy', 'require-corp');
        $httpResponse->setHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $httpResponse->setHeader('Cross-Origin-Resource-Policy', 'same-origin');
        $httpResponse->setHeader('X-DNS-Prefetch-Control', 'off');

        // Ochrana před odhalením informací o serveru
        $httpResponse->setHeader('Server', '');
    }

    /**
     * 🔒 Vynutí HTTPS pouze v produkčním prostředí
     */
    private static function enforceHttpsInProduction(): void
    {
        // Kontrola prostředí - jen produkce
        if (!self::isProductionEnvironment()) {
            return;
        }

        // Kontrola, zda už je HTTPS
        if (self::isHttpsRequest()) {
            return;
        }

        // Redirect HTTP → HTTPS
        $httpsUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . $httpsUrl, true, 301);
        exit;
    }

    /**
     * 🔒 Kontroluje, zda je požadavek přes HTTPS
     */
    private static function isHttpsRequest(): bool
    {
        return (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        );
    }

    /**
     * 🔒 Kontroluje, zda je produkční prostředí
     */
    private static function isProductionEnvironment(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Localhost = ne produkce
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            return false;
        }

        // Kontrola .env
        $environment = $_ENV['ENVIRONMENT'] ?? 'development';
        return $environment === 'production';
    }
}
