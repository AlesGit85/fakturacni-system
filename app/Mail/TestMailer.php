<?php

declare(strict_types=1);

namespace App\Mail;

use Nette;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Testovací mailer pro development
 * Ukládá emaily do souborů a loguje je
 */
class TestMailer implements Mailer
{
    /** @var string */
    private $directory;

    public function __construct(string $tempDir)
    {
        $this->directory = $tempDir . '/mails';
        
        // Vytvoření adresáře pokud neexistuje
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0755, true);
        }
    }

    public function send(Message $mail): void
    {
        $filename = $this->directory . '/' . date('Y-m-d_H-i-s_') . uniqid() . '.eml';
        
        // Uložení emailu do souboru
        file_put_contents($filename, $mail->generateMessage());
        
        // Logování pro debug
        $subject = $mail->getSubject();
        $to = implode(', ', array_keys($mail->getHeader('To')));
        
        error_log("TEST MAILER: Email odeslán - Komu: {$to}, Předmět: {$subject}, Soubor: {$filename}");
        
        // Výpis do Tracy baru (pokud je zapnutý)
        if (class_exists('\Tracy\Debugger')) {
            \Tracy\Debugger::barDump("Email uložen: {$filename}", "Test Mailer");
            \Tracy\Debugger::barDump("Komu: {$to}", "Test Mailer");
            \Tracy\Debugger::barDump("Předmět: {$subject}", "Test Mailer");
        }
    }
}