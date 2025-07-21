<?php

declare(strict_types=1);

namespace App\Presentation\Accessory;

use Latte\Extension;
use Latte\Runtime\Html;
use App\Security\SecurityValidator;

/**
 * ✅ ROZŠÍŘENÁ Latte Extension s bezpečnostními funkcemi
 * Nahrazuje nebezpečné použití |noescape filtru
 */
final class LatteExtension extends Extension
{
    /** @var array Povolené Bootstrap Icons třídy */
    private static array $allowedIconClasses = [
        // Základní ikony
        'bi-house', 'bi-people', 'bi-file-earmark-text', 'bi-gear', 'bi-plus-circle',
        'bi-pencil-square', 'bi-trash', 'bi-eye', 'bi-download', 'bi-upload',
        'bi-search', 'bi-filter', 'bi-sort-down', 'bi-sort-up', 'bi-arrow-left',
        'bi-arrow-right', 'bi-arrow-up', 'bi-arrow-down', 'bi-chevron-left',
        'bi-chevron-right', 'bi-chevron-up', 'bi-chevron-down',
        
        // Ikony s prefixem fill
        'bi-house-fill', 'bi-people-fill', 'bi-file-earmark-text-fill',
        'bi-gear-fill', 'bi-plus-circle-fill', 'bi-pencil-square-fill',
        'bi-trash-fill', 'bi-eye-fill', 'bi-search-fill',
        
        // Business ikony
        'bi-building', 'bi-building-fill', 'bi-person', 'bi-person-fill',
        'bi-person-circle', 'bi-envelope', 'bi-envelope-fill', 'bi-phone',
        'bi-phone-fill', 'bi-calendar', 'bi-calendar-fill', 'bi-clock',
        'bi-clock-fill', 'bi-currency-dollar', 'bi-currency-euro',
        
        // Status ikony
        'bi-check', 'bi-check-circle', 'bi-check-circle-fill', 'bi-x',
        'bi-x-circle', 'bi-x-circle-fill', 'bi-exclamation-triangle',
        'bi-exclamation-triangle-fill', 'bi-info-circle', 'bi-info-circle-fill',
        'bi-shield', 'bi-shield-fill', 'bi-shield-check', 'bi-shield-check-fill',
        
        // Navigační ikony
        'bi-list', 'bi-grid', 'bi-grid-fill', 'bi-table', 'bi-kanban',
        'bi-kanban-fill', 'bi-diagram-3', 'bi-diagram-3-fill',
        
        // Akční ikony
        'bi-save', 'bi-save-fill', 'bi-copy', 'bi-clipboard', 'bi-clipboard-fill',
        'bi-share', 'bi-share-fill', 'bi-print', 'bi-printer', 'bi-printer-fill',
        
        // Moduly a rozšíření
        'bi-puzzle', 'bi-puzzle-fill', 'bi-plugin', 'bi-tools', 'bi-wrench',
        'bi-hammer', 'bi-code', 'bi-code-slash', 'bi-terminal',
        
        // Finanční ikony
        'bi-graph-up', 'bi-graph-down', 'bi-bar-chart', 'bi-bar-chart-fill',
        'bi-pie-chart', 'bi-pie-chart-fill', 'bi-receipt', 'bi-receipt-cutoff',
        'bi-calculator', 'bi-calculator-fill', 'bi-credit-card',
        'bi-credit-card-fill', 'bi-wallet', 'bi-wallet-fill',
        
        // Dokumenty a soubory
        'bi-file', 'bi-file-fill', 'bi-file-text', 'bi-file-text-fill',
        'bi-file-pdf', 'bi-file-pdf-fill', 'bi-file-excel', 'bi-file-excel-fill',
        'bi-folder', 'bi-folder-fill', 'bi-folder-open', 'bi-folder-open-fill',
        
        // Komunikace
        'bi-chat', 'bi-chat-fill', 'bi-chat-dots', 'bi-chat-dots-fill',
        'bi-telephone', 'bi-telephone-fill', 'bi-at', 'bi-globe',
        
        // Bezpečnost
        'bi-lock', 'bi-lock-fill', 'bi-unlock', 'bi-unlock-fill',
        'bi-key', 'bi-key-fill', 'bi-fingerprint', 'bi-shield-lock',
        'bi-shield-lock-fill', 'bi-shield-exclamation', 'bi-shield-exclamation-fill',
        
        // Uživatelé a týmy
        'bi-person-badge', 'bi-person-badge-fill', 'bi-person-check',
        'bi-person-check-fill', 'bi-person-plus', 'bi-person-plus-fill',
        'bi-person-dash', 'bi-person-dash-fill', 'bi-people-circle',
        
        // Čas a datum
        'bi-calendar-date', 'bi-calendar-date-fill', 'bi-calendar-event',
        'bi-calendar-event-fill', 'bi-clock-history', 'bi-stopwatch',
        'bi-stopwatch-fill', 'bi-hourglass', 'bi-hourglass-split',
        
        // Místa a lokace
        'bi-geo', 'bi-geo-fill', 'bi-geo-alt', 'bi-geo-alt-fill',
        'bi-map', 'bi-map-fill', 'bi-compass', 'bi-compass-fill',
        
        // Sítě a připojení
        'bi-wifi', 'bi-wifi-off', 'bi-router', 'bi-router-fill',
        'bi-ethernet', 'bi-broadcast', 'bi-broadcast-pin',
        
        // Oblíbené a hodnocení
        'bi-star', 'bi-star-fill', 'bi-star-half', 'bi-heart',
        'bi-heart-fill', 'bi-bookmark', 'bi-bookmark-fill',
        
        // Médiá
        'bi-image', 'bi-image-fill', 'bi-camera', 'bi-camera-fill',
        'bi-film', 'bi-music-note', 'bi-music-note-beamed',
        
        // Různé
        'bi-question', 'bi-question-circle', 'bi-question-circle-fill',
        'bi-lightbulb', 'bi-lightbulb-fill', 'bi-flag', 'bi-flag-fill',
        'bi-tag', 'bi-tag-fill', 'bi-tags', 'bi-tags-fill',
        
        // UPC a kódy
        'bi-upc', 'bi-upc-scan', 'bi-qr-code', 'bi-qr-code-scan',
        
        // Box arrow ikony (důležité pro odhlášení atd.)
        'bi-box-arrow-right', 'bi-box-arrow-left', 'bi-box-arrow-up',
        'bi-box-arrow-down', 'bi-box-arrow-in-right', 'bi-box-arrow-in-left',
        'bi-box-arrow-in-up', 'bi-box-arrow-in-down',
        
        // ✅ NOVĚ PŘIDÁNO: Další často používané ikony
        'bi-pause-circle', 'bi-play-circle', 'bi-stop-circle',
        'bi-cloud-upload', 'bi-cloud-download', 'bi-database',
        'bi-server', 'bi-laptop', 'bi-tablet', 'bi-phone',
        'bi-envelope-open', 'bi-envelope-open-fill', 'bi-bell',
        'bi-bell-fill', 'bi-bookmark-star', 'bi-bookmark-star-fill',
        'bi-award', 'bi-award-fill', 'bi-trophy', 'bi-trophy-fill'
    ];

    public function getFilters(): array
    {
        return [
            // ✅ NOVÉ: Bezpečnostní filtry
            'safeIcon' => [$this, 'filterSafeIcon'],
            'safeHtml' => [$this, 'filterSafeHtml'],
        ];
    }

    public function getFunctions(): array
    {
        return [
            // ✅ NOVÉ: Bezpečnostní funkce
            'safeIcon' => [$this, 'functionSafeIcon'],
            'safeOnclick' => [$this, 'functionSafeOnclick'],
            'isValidIcon' => [$this, 'functionIsValidIcon'],
        ];
    }

    // =====================================================================
    // ✅ NOVÉ: Bezpečnostní filtry
    // =====================================================================

    /**
     * Filter pro bezpečné zobrazení ikon
     * Použití v šablonách: {$iconClass|safeIcon}
     */
    public function filterSafeIcon(string $iconClass): Html
    {
        return $this->functionSafeIcon($iconClass);
    }

    /**
     * Filter pro bezpečné zobrazení HTML (pouze povolené tagy)
     * Použití v šablonách: {$content|safeHtml}
     */
    public function filterSafeHtml(string $content): string
    {
        return SecurityValidator::sanitizeRichText($content);
    }

    // =====================================================================
    // ✅ NOVÉ: Bezpečnostní funkce
    // =====================================================================

    /**
     * Funkce pro bezpečné zobrazení ikon
     * Použití v šablonách: {safeIcon($iconClass)}
     * 
     * Nahrazuje: {$iconClass|noescape}
     */
    public function functionSafeIcon(string $iconClass): Html
    {
        // Sanitizace vstupní třídy
        $iconClass = SecurityValidator::sanitizeString($iconClass);
        
        // Kontrola, zda je ikona v povolených třídách
        if (!$this->isIconAllowed($iconClass)) {
            // Pokud ikona není povolena, použije se výchozí
            $iconClass = 'bi-question-circle';
        }
        
        // Vrátíme jako bezpečný HTML
        return new Html('<i class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '"></i>');
    }

    /**
     * Funkce pro bezpečné onclick eventy
     * Používá whitelist povolených funkcí
     * 
     * Nahrazuje: {$onclick|noescape}
     */
    public function functionSafeOnclick(string $onclick): string
    {
        // Sanitizace
        $onclick = SecurityValidator::sanitizeString($onclick);
        
        // Whitelist povolených JavaScript funkcí
        $allowedFunctions = [
            'confirm',
            'alert', 
            'window.open',
            'history.back',
            'history.forward',
            'location.reload',
            'console.log',
            'document.getElementById',
            'this.submit',
            'this.reset',
            'return false',
            'return true',
        ];
        
        // Kontrola, zda onclick obsahuje pouze povolené funkce
        $isAllowed = false;
        foreach ($allowedFunctions as $allowedFunction) {
            if (strpos($onclick, $allowedFunction) !== false) {
                $isAllowed = true;
                break;
            }
        }
        
        // Pokud obsahuje nepovolené funkce, vraťme prázdný string
        if (!$isAllowed) {
            return '';
        }
        
        // Dodatečná kontrola na nebezpečné výrazy
        $dangerousPatterns = [
            '/eval\s*\(/i',
            '/document\.write\s*\(/i',
            '/innerHTML\s*=/i',
            '/outerHTML\s*=/i',
            '/setTimeout\s*\(/i',
            '/setInterval\s*\(/i',
            '/Function\s*\(/i',
            '/new\s+Function/i',
            '/<script/i',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $onclick)) {
                return '';
            }
        }
        
        return $onclick;
    }

    /**
     * Funkce pro kontrolu, zda je ikona validní
     * Použití v šablonách: {if isValidIcon($iconClass)}...{/if}
     */
    public function functionIsValidIcon(string $iconClass): bool
    {
        return $this->isIconAllowed($iconClass);
    }

    // =====================================================================
    // ✅ NOVÉ: Privátní pomocné metody
    // =====================================================================

    /**
     * Kontroluje, zda je ikona v seznamu povolených
     */
    private function isIconAllowed(string $iconClass): bool
    {
        // Očistíme třídu od extra mezer a prefixů
        $iconClass = trim($iconClass);
        
        // Pokud má prefix 'bi ', odstraníme ho pro kontrolu
        if (str_starts_with($iconClass, 'bi ')) {
            $iconClass = str_replace('bi ', '', $iconClass);
        }
        
        // Rozdělíme na jednotlivé třídy (pokud jich je víc)
        $classes = explode(' ', $iconClass);
        
        foreach ($classes as $class) {
            $class = trim($class);
            
            // Přeskočíme prázdné třídy
            if (empty($class)) {
                continue;
            }
            
            // Přeskočíme obecné CSS třídy (me-2, ms-auto, atd.)
            if ($this->isGenericCssClass($class)) {
                continue;
            }
            
            // Pokud třída nezačína 'bi-', přidáme prefix pro kontrolu
            if (!str_starts_with($class, 'bi-')) {
                $class = 'bi-' . $class;
            }
            
            // Kontrola v whitelist
            if (!in_array($class, self::$allowedIconClasses)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Kontroluje, zda je třída obecná CSS třída (Bootstrap spacing, atd.)
     */
    private function isGenericCssClass(string $class): bool
    {
        $genericPatterns = [
            '/^me-\d+$/',    // me-1, me-2, me-3...
            '/^ms-\d+$/',    // ms-1, ms-2, ms-3...
            '/^m-\d+$/',     // m-1, m-2, m-3...
            '/^p-\d+$/',     // p-1, p-2, p-3...
            '/^text-.+$/',   // text-primary, text-danger...
            '/^bg-.+$/',     // bg-primary, bg-secondary...
            '/^fs-\d+$/',    // fs-1, fs-2, fs-3...
            '/^fw-.+$/',     // fw-bold, fw-normal...
            '/^d-.+$/',      // d-flex, d-none...
            '/^align-.+$/',  // align-center, align-items-center...
            '/^justify-.+$/', // justify-content-center...
            '/^border-.+$/', // border-0, border-primary...
            '/^rounded-.+$/', // rounded-0, rounded-circle...
        ];
        
        foreach ($genericPatterns as $pattern) {
            if (preg_match($pattern, $class)) {
                return true;
            }
        }
        
        return false;
    }
}