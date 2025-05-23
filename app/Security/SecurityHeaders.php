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
}