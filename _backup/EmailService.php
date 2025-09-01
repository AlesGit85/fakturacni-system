<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Application\LinkGenerator;

/**
 * Služba pro odesílání emailů
 */
class EmailService
{
    /** @var Mailer */
    private $mailer;

    /** @var LinkGenerator */
    private $linkGenerator;

    /** @var string */
    private $appName;

    /** @var string */
    private $fromEmail;

    /** @var string */
    private $adminEmail;

    public function __construct(
        Mailer $mailer,
        LinkGenerator $linkGenerator,
        string $appName = 'QRdoklad',
        string $fromEmail = 'noreply@allimedia.cz',
        string $adminEmail = 'info@allimedia.cz'
    ) {
        $this->mailer = $mailer;
        $this->linkGenerator = $linkGenerator;
        $this->appName = $appName;
        $this->fromEmail = $fromEmail;
        $this->adminEmail = $adminEmail;
    }

    /**
     * Odešle email s potvrzením registrace uživateli
     */
    public function sendRegistrationConfirmation(string $userEmail, string $username, string $role): void
    {
        $mail = new Message;
        $mail->setFrom($this->fromEmail, $this->appName)
            ->addTo($userEmail)
            ->setSubject('Vítejte v ' . $this->appName . ' - registrace byla úspěšná');

        // Získání role v češtině
        $roleNames = [
            'admin' => 'Administrátor',
            'accountant' => 'Účetní',
            'readonly' => 'Pouze čtení'
        ];
        $roleName = $roleNames[$role] ?? $role;

        $loginUrl = $this->linkGenerator->link('Sign:in');

        // HTML verze
        $htmlBody = $this->createRegistrationConfirmationHtml($username, $roleName, $loginUrl);
        $mail->setHtmlBody($htmlBody);

        // Textová verze emailu
        $textBody = "Vítejte v {$this->appName}!\n\n";
        $textBody .= "Váš účet s uživatelským jménem '{$username}' byl úspěšně vytvořen.\n";
        $textBody .= "Role: {$roleName}\n\n";
        $textBody .= "Nyní se můžete přihlásit: {$loginUrl}\n\n";
        $textBody .= "V případě problémů nás kontaktujte na: {$this->adminEmail}";
        
        $mail->setBody($textBody);

        $this->mailer->send($mail);
    }

    /**
     * Odešle upozornění adminovi o nové registraci
     */
    public function sendAdminNotification(string $username, string $email, string $role): void
    {
        $mail = new Message;
        $mail->setFrom($this->fromEmail, $this->appName)
            ->addTo($this->adminEmail)
            ->setSubject('Nová registrace v ' . $this->appName);

        // Získání role v češtině
        $roleNames = [
            'admin' => 'Administrátor',
            'accountant' => 'Účetní',
            'readonly' => 'Pouze čtení'
        ];
        $roleName = $roleNames[$role] ?? $role;

        $usersUrl = $this->linkGenerator->link('Users:default');

        // HTML verze
        $htmlBody = $this->createAdminNotificationHtml($username, $email, $roleName, $usersUrl);
        $mail->setHtmlBody($htmlBody);

        // Textová verze emailu
        $textBody = "Nová registrace v {$this->appName}\n\n";
        $textBody .= "Uživatelské jméno: {$username}\n";
        $textBody .= "E-mail: {$email}\n";
        $textBody .= "Role: {$roleName}\n";
        $textBody .= "Čas registrace: " . date('d.m.Y H:i:s') . "\n\n";
        $textBody .= "Přehled uživatelů: {$usersUrl}";
        
        $mail->setBody($textBody);

        $this->mailer->send($mail);
    }

    /**
     * Odešle email pro resetování hesla
     */
    public function sendPasswordReset(string $userEmail, string $username, string $resetToken): void
    {
        $mail = new Message;
        $mail->setFrom($this->fromEmail, $this->appName)
            ->addTo($userEmail)
            ->setSubject('Obnovení hesla - ' . $this->appName);

        $resetUrl = $this->linkGenerator->link('Sign:resetPassword', ['token' => $resetToken]);

        // HTML verze
        $htmlBody = $this->createPasswordResetHtml($username, $resetUrl);
        $mail->setHtmlBody($htmlBody);

        // Textová verze emailu
        $textBody = "Obnovení hesla pro {$this->appName}\n\n";
        $textBody .= "Ahoj {$username},\n\n";
        $textBody .= "Někdo požádal o obnovení hesla pro váš účet.\n";
        $textBody .= "Pokud to nebyli vy, tento email ignorujte.\n\n";
        $textBody .= "Pro obnovení hesla klikněte na následující odkaz:\n";
        $textBody .= "{$resetUrl}\n\n";
        $textBody .= "Odkaz je platný po dobu 24 hodin.\n\n";
        $textBody .= "V případě problémů nás kontaktujte na: {$this->adminEmail}";
        
        $mail->setBody($textBody);

        $this->mailer->send($mail);
    }

    /**
     * Test metoda pro ověření funkčnosti emailové služby
     */
    public function sendTestEmail(string $email): void
    {
        $mail = new Message;
        $mail->setFrom($this->fromEmail, $this->appName)
            ->addTo($email)
            ->setSubject('Test email - ' . $this->appName);

        // HTML verze
        $htmlBody = $this->createTestEmailHtml();
        $mail->setHtmlBody($htmlBody);

        // Textová verze
        $textBody = "Testovací email ze systému {$this->appName}.\n\n";
        $textBody .= "Pokud tento email vidíte, emailová služba funguje správně.\n\n";
        $textBody .= "Čas odeslání: " . date('d.m.Y H:i:s');
        
        $mail->setBody($textBody);

        $this->mailer->send($mail);
    }

    /**
     * Vytvoří HTML pro potvrzení registrace
     */
    private function createRegistrationConfirmationHtml(string $username, string $roleName, string $loginUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #212529; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #B1D235; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .btn { background: #B1D235; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Vítejte v {$this->appName}!</h1>
                </div>
                <div class='content'>
                    <h2>Registrace byla úspěšná</h2>
                    <p>Váš účet s uživatelským jménem <strong>{$username}</strong> byl úspěšně vytvořen.</p>
                    <p><strong>Role:</strong> {$roleName}</p>
                    
                    <p>Nyní se můžete přihlásit a začít používat systém:</p>
                    <a href='{$loginUrl}' class='btn'>Přihlásit se</a>
                    
                    <p>V případě problémů nás kontaktujte na: <a href='mailto:{$this->adminEmail}'>{$this->adminEmail}</a></p>
                </div>
                <div class='footer'>
                    <p>Tento email byl automaticky vygenerován systémem {$this->appName}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Vytvoří HTML pro admin notifikaci
     */
    private function createAdminNotificationHtml(string $username, string $email, string $roleName, string $usersUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #212529; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #95B11F; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .info { background: white; padding: 15px; border-left: 4px solid #B1D235; margin: 20px 0; }
                .btn { background: #95B11F; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Nová registrace</h1>
                </div>
                <div class='content'>
                    <p>V systému {$this->appName} se zaregistroval nový uživatel:</p>
                    
                    <div class='info'>
                        <p><strong>Uživatelské jméno:</strong> {$username}</p>
                        <p><strong>E-mail:</strong> {$email}</p>
                        <p><strong>Role:</strong> {$roleName}</p>
                        <p><strong>Čas registrace:</strong> " . date('d.m.Y H:i:s') . "</p>
                    </div>
                    
                    <a href='{$usersUrl}' class='btn'>Správa uživatelů</a>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Vytvoří HTML pro reset hesla
     */
    private function createPasswordResetHtml(string $username, string $resetUrl): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #212529; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #6c757d; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .btn { background: #B1D235; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; border-radius: 5px; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Obnovení hesla</h1>
                </div>
                <div class='content'>
                    <p>Ahoj <strong>{$username}</strong>,</p>
                    <p>někdo požádal o obnovení hesla pro váš účet.</p>
                    
                    <div class='warning'>
                        <strong>Pokud to nebyli vy, tento email ignorujte.</strong>
                    </div>
                    
                    <p>Pro obnovení hesla klikněte na tlačítko:</p>
                    <a href='{$resetUrl}' class='btn'>Obnovit heslo</a>
                    
                    <p><small>Odkaz je platný po dobu 24 hodin.</small></p>
                    <p>V případě problémů nás kontaktujte na: <a href='mailto:{$this->adminEmail}'>{$this->adminEmail}</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Vytvoří HTML pro testovací email
     */
    private function createTestEmailHtml(): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #212529; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #B1D235; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; text-align: center; }
                .success { color: #155724; background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Test Email</h1>
                </div>
                <div class='content'>
                    <div class='success'>
                        <h2>Emailová služba funguje správně!</h2>
                    </div>
                    <p>Tento testovací email ze systému <strong>{$this->appName}</strong> potvrzuje, že odesílání emailů funguje.</p>
                    <p><small>Čas odeslání: " . date('d.m.Y H:i:s') . "</small></p>
                </div>
            </div>
        </body>
        </html>";
    }
}