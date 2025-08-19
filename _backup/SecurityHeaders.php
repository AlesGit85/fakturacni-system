<?php

declare(strict_types=1);

namespace App\Security;

use Nette;

/**
 * Třída pro nastavení bezpečnostních HTTP hlaviček
 */
class SecurityHeaders
{
    /**
     * Aplikuje bezpečnostní hlavičky na HTTP odpověď
     */
    public static function apply(Nette\Http\Response $httpResponse): void
    {
        // Zakáže MIME-sniffing v prohlížečích
        $httpResponse->setHeader('X-Content-Type-Options', 'nosniff');

        // Chrání před clickjacking útoky
        $httpResponse->setHeader('X-Frame-Options', 'DENY');

        // Nastavuje politiku referrerů
        $httpResponse->setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Chrání před XSS útoky (může být potřeba upravit podle potřeb aplikace)
        $httpResponse->setHeader('X-XSS-Protection', '1; mode=block');

        // Content Security Policy - nastavuje pravidla pro načítání zdrojů
        // Toto je základní konfigurace, kterou můžete upravit podle potřeb vaší aplikace
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

        // Ochrana proti CSRF útokům (již implementováno v Nette)
        // Ochrana před odhalením informací o serveru
        $httpResponse->setHeader('Server', '');

        // HTTP Strict Transport Security - vynutí HTTPS (pro produkční nasazení)
        // $httpResponse->setHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }

    /**
     * 🔒 NOVÉ: Vynutí HTTPS pouze v produkčním prostředí
     */
    private static function enforceHttpsInProduction(): void
    {
        // Kontrola prostředí - jen produkce
        if (!self::isProductionEnvironment()) {
            return; // Na localhostu/dev nic nedělá
        }

        // Kontrola, zda už je HTTPS
        if (self::isHttpsRequest()) {
            return; // Už je HTTPS, OK
        }

        // Redirect HTTP → HTTPS
        $httpsUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . $httpsUrl, true, 301);
        exit;
    }

    /**
     * 🔒 NOVÉ: Kontroluje, zda je požadavek přes HTTPS
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
     * 🔒 NOVÉ: Kontroluje, zda je produkční prostředí
     */
    private static function isProductionEnvironment(): bool
    {
        // Kontrola domény
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            return false; // Localhost = ne produkce
        }

        // Kontrola .env
        $isProduction = $_ENV['ENVIRONMENT'] ?? 'development';
        return $isProduction === 'production';
    }
}
