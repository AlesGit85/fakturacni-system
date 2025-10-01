<?php

declare(strict_types=1);

namespace App\Mail;

use Nette;
use Nette\Mail\SmtpMailer;

/**
 * Factory pro vytvoření SMTP Maileru
 */
class SmtpFactory
{
    public static function create(): SmtpMailer
    {
        // Načtení environment proměnných
        $username = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '';
        $password = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '';
        
        // Validace
        if (empty($username) || empty($password)) {
            throw new \Exception("SMTP přihlašovací údaje nejsou nastaveny. Zkontrolujte SMTP_USERNAME a SMTP_PASSWORD v .env souboru. Username: '$username', Password: " . (empty($password) ? 'prázdné' : 'nastaveno'));
        }
        
        // Vytvoření SmtpMailer se správným pořadím parametrů
        return new SmtpMailer(
            host: 'smtp.seznam.cz',
            username: $username,
            password: $password,
            port: 465,
            encryption: 'ssl',
            timeout: 30
        );
    }
}