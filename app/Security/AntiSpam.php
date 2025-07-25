<?php

declare(strict_types=1);

namespace App\Security;

use Nette;
use App\Security\SecurityLogger;

/**
 * Anti-Spam ochrana pro formuláře
 * Implementuje honeypot a další anti-spam techniky
 */
class AntiSpam
{
    use Nette\SmartObject;

    /** @var SecurityLogger */
    private $securityLogger;

    /** @var array Názvy honeypot polí (rotujeme je pro větší bezpečnost) */
    private $honeypotFieldNames = [
        'website_url',       // Vypadá legitimně
        'company_website',   // Pro firemní formuláře
        'additional_info',   // Obecné info pole
        'secondary_email',   // Druhý email
        'backup_phone',      // Záložní telefon
        'referral_source',   // Zdroj doporučení
    ];

    /** @var int Minimální čas vyplnění formuláře (sekundy) */
    private $minSubmissionTime = 3;

    /** @var int Maximální čas vyplnění formuláře (sekundy) */
    private $maxSubmissionTime = 3600; // 1 hodina

    public function __construct(SecurityLogger $securityLogger)
    {
        $this->securityLogger = $securityLogger;
    }

    /**
     * ✅ NOVÉ: Přidá honeypot pole do formuláře
     */
    public function addHoneypotToForm(Nette\Application\UI\Form $form): string
    {
        // Vybere náhodný název honeypot pole
        $fieldName = $this->getRandomHoneypotFieldName();
        
        // Přidá skryté pole s instrukcí pro screen readery
        $honeypotField = $form->addText($fieldName, 'Nevyplňujte toto pole (anti-spam)')
            ->setHtmlAttribute('class', 'honeypot-field')
            ->setHtmlAttribute('tabindex', '-1')
            ->setHtmlAttribute('autocomplete', 'off')
            ->setHtmlAttribute('aria-hidden', 'true');

        // Přidá validační pravidlo
        $honeypotField->addRule(function ($control) use ($fieldName) {
            $value = trim($control->getValue());
            
            // Pokud je pole vyplněné, jedná se o spam
            if (!empty($value)) {
                $this->logSpamAttempt('honeypot', $fieldName, [
                    'honeypot_field' => $fieldName,
                    'honeypot_value' => substr($value, 0, 100), // Jen preview
                    'form_name' => $control->getForm()->getName() ?? 'unknown'
                ]);
                return false;
            }
            
            return true;
        }, 'Spam detekce aktivní. Pokud jste člověk, kontaktujte administrátora.');

        return $fieldName;
    }

    /**
     * ✅ NOVÉ: Přidá timing ochranu proti rychlému odesílání
     */
    public function addTimingProtection(Nette\Application\UI\Form $form): void
    {
        // Přidá hidden field s timestamp
        $timestampField = $form->addHidden('form_timestamp', (string)time());
        
        // Přidá validaci času
        $timestampField->addRule(function ($control) {
            $timestamp = (int)$control->getValue();
            $currentTime = time();
            $submissionTime = $currentTime - $timestamp;
            
            // Příliš rychlé odeslání (bot)
            if ($submissionTime < $this->minSubmissionTime) {
                $this->logSpamAttempt('timing_too_fast', '', [
                    'submission_time' => $submissionTime,
                    'min_required' => $this->minSubmissionTime
                ]);
                return false;
            }
            
            // Příliš dlouhé čekání (možná útok)
            if ($submissionTime > $this->maxSubmissionTime) {
                $this->logSpamAttempt('timing_too_slow', '', [
                    'submission_time' => $submissionTime,
                    'max_allowed' => $this->maxSubmissionTime
                ]);
                return false;
            }
            
            return true;
        }, 'Formulář byl odeslán příliš rychle nebo po příliš dlouhém čase. Zkuste to znovu.');
    }

    /**
     * ✅ OPRAVENO: Kompletní kontrola formuláře proti spamu - s kontrolou validity
     */
    public function validateFormAgainstSpam(Nette\Application\UI\Form $form): bool
    {
        // ✅ KRITICKÁ OPRAVA: Nejdříve zkontrolujeme, zda je formulář validní
        // Pokud honeypot pravidla už označila formulář jako nevalidní, nevoláme getValues()
        if (!$form->isValid()) {
            // Formulář už je nevalidní (např. kvůli honeypot), nemusíme kontrolovat další vzory
            return false;
        }

        try {
            $formData = $form->getValues('array');
            
            // Kontrola podezřelých vzorů
            $suspiciousPatterns = $this->detectSpamPatterns($formData);
            
            // Pokud najdeme příliš mnoho podezřelých vzorů, označíme jako spam
            $totalPatterns = array_sum(array_map('count', $suspiciousPatterns));
            
            if ($totalPatterns >= 3) { // Práh pro označení jako spam
                $form->addError('Formulář obsahuje podezřelý obsah. Pokud jste člověk, kontaktujte administrátora.');
                return false;
            }
            
            return true;
            
        } catch (\Exception $e) {
            // Pokud se něco pokazí, neblokujeme formulář
            error_log('Chyba při validaci anti-spam: ' . $e->getMessage());
            return true;
        }
    }

    /**
     * ✅ NOVÉ: Kontrola podezřelých vzorů v datech
     */
    public function detectSpamPatterns(array $formData): array
    {
        $suspiciousPatterns = [];
        
        foreach ($formData as $fieldName => $value) {
            if (!is_string($value)) {
                continue;
            }
            
            $value = trim($value);
            
            // Prázdné hodnoty přeskočíme
            if (empty($value)) {
                continue;
            }
            
            // Detekce podezřelých vzorů
            $patterns = $this->checkSpamPatterns($value);
            if (!empty($patterns)) {
                $suspiciousPatterns[$fieldName] = $patterns;
            }
        }
        
        // Logování pokud najdeme podezřelé vzory
        if (!empty($suspiciousPatterns)) {
            $this->logSpamAttempt('suspicious_patterns', '', [
                'patterns_found' => $suspiciousPatterns,
                'form_data_preview' => $this->getFormDataPreview($formData)
            ]);
        }
        
        return $suspiciousPatterns;
    }

    /**
     * ✅ NOVÉ: Kontrola jednotlivých spam vzorů
     */
    private function checkSpamPatterns(string $text): array
    {
        $foundPatterns = [];
        
        // Vzory typické pro spam
        $spamPatterns = [
            'excessive_urls' => '/https?:\/\/[^\s]+.*https?:\/\/[^\s]+/', // Více URL
            'excessive_caps' => '/[A-Z]{10,}/', // Příliš mnoho velkých písmen
            'excessive_exclamation' => '/!{3,}/', // Více vykřičníků
            'phone_like_numbers' => '/\b\d{3,4}[-.\s]?\d{3,4}[-.\s]?\d{3,4}\b/', // Telefonní čísla
            'email_like_pattern' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', // Email adresy
            'excessive_numbers' => '/\d{10,}/', // Dlouhé číselné sekvence
            'pharmaceutical_terms' => '/\b(viagra|cialis|pharmacy|pills|medication)\b/i', // Farmaceutické termíny
            'gambling_terms' => '/\b(casino|poker|lottery|jackpot|betting)\b/i', // Hazardní termíny
            'financial_scam' => '/\b(loan|money|cash|investment|profit|rich)\b/i', // Finanční spam
        ];
        
        foreach ($spamPatterns as $patternName => $pattern) {
            if (preg_match($pattern, $text)) {
                $foundPatterns[] = $patternName;
            }
        }
        
        return $foundPatterns;
    }

    /**
     * ✅ NOVÉ: Získá náhodný název honeypot pole
     */
    private function getRandomHoneypotFieldName(): string
    {
        return $this->honeypotFieldNames[array_rand($this->honeypotFieldNames)];
    }

    /**
     * ✅ NOVÉ: Vytvoří preview formulářových dat pro logování
     */
    private function getFormDataPreview(array $formData): array
    {
        $preview = [];
        
        foreach ($formData as $key => $value) {
            if (is_string($value)) {
                $preview[$key] = substr($value, 0, 50) . (strlen($value) > 50 ? '...' : '');
            } else {
                $preview[$key] = gettype($value);
            }
        }
        
        return $preview;
    }

    /**
     * ✅ NOVÉ: Logování spam pokusu
     */
    private function logSpamAttempt(string $type, string $field, array $details = []): void
    {
        $this->securityLogger->logSecurityEvent(
            'spam_attempt',
            "Anti-spam detekce: {$type}" . ($field ? " v poli '{$field}'" : ''),
            array_merge([
                'spam_type' => $type,
                'field_name' => $field,
                'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'timestamp' => date('Y-m-d H:i:s'),
            ], $details)
        );
    }

    /**
     * ✅ NOVÉ: Získání statistik anti-spam systému
     */
    public function getAntiSpamStats(): array
    {
        // TODO: Implementovat když bude potřeba pro dashboard
        return [
            'honeypot_blocks_today' => 0,
            'timing_blocks_today' => 0,
            'pattern_blocks_today' => 0,
            'total_blocks_today' => 0
        ];
    }
}