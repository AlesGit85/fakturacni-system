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

    /** @var string Výchozí from email (fallback když tenant nemá nastavený) */
    private $defaultFromEmail;

    /** @var string Výchozí admin email (fallback když tenant nemá nastavený) */
    private $defaultAdminEmail;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var int|null Aktuální tenant ID pro kontext */
    private $currentTenantId = null;

    public function __construct(
        Mailer $mailer,
        LinkGenerator $linkGenerator,
        Nette\Database\Explorer $database,
        string $appName = 'QRdoklad',
        string $defaultFromEmail = 'noreply@qrdoklad.cz',
        string $defaultAdminEmail = 'info@qrdoklad.cz'
    ) {
        $this->mailer = $mailer;
        $this->linkGenerator = $linkGenerator;
        $this->database = $database;
        $this->appName = $appName;
        $this->defaultFromEmail = $defaultFromEmail;
        $this->defaultAdminEmail = $defaultAdminEmail;
    }

    /**
     * Nastaví kontext tenanta pro správné emaily
     */
    public function setTenantContext(?int $tenantId): void
    {
        $this->currentTenantId = $tenantId;
    }

    /**
     * Získá from email pro aktuální tenant
     */
    private function getFromEmail(): string
    {
        if ($this->currentTenantId) {
            $tenant = $this->database->table('tenants')
                ->where('id', $this->currentTenantId)
                ->fetch();

            if ($tenant && !empty($tenant->email_from)) {
                return $tenant->email_from;
            }
        }

        return $this->defaultFromEmail;
    }

    /**
     * Získá from název pro aktuální tenant
     */
    private function getFromName(): string
    {
        if ($this->currentTenantId) {
            $tenant = $this->database->table('tenants')
                ->where('id', $this->currentTenantId)
                ->fetch();

            if ($tenant && !empty($tenant->company_name)) {
                return $tenant->company_name;
            }
        }

        return $this->appName;
    }

    /**
     * Získá admin email pro aktuální tenant
     */
    private function getAdminEmail(): string
    {
        if ($this->currentTenantId) {
            $tenant = $this->database->table('tenants')
                ->where('id', $this->currentTenantId)
                ->fetch();

            if ($tenant && !empty($tenant->admin_email)) {
                return $tenant->admin_email;
            }
        }

        return $this->defaultAdminEmail;
    }


    /**
     * Odešle email s potvrzením registrace uživateli
     * PRO REGISTRACI: vždy používáme defaultFromEmail, ne tenant-specific
     */
    public function sendRegistrationConfirmation(string $userEmail, string $username, string $role): void
    {
        $mail = new Message;

        // PRO REGISTRACI: Vždy použijeme default hodnoty místo tenant-specific
        $mail->setFrom($this->defaultFromEmail, $this->appName)
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

        // HTML verze - používáme default admin email
        $htmlBody = $this->createRegistrationConfirmationHtml($username, $roleName, $loginUrl, $this->defaultAdminEmail);
        $mail->setHtmlBody($htmlBody);

        // Textová verze
        $textBody = "Vítejte v " . $this->appName . "!\n\n";
        $textBody .= "Váš účet s uživatelským jménem '{$username}' byl úspěšně vytvořen.\n";
        $textBody .= "Role: {$roleName}\n\n";
        $textBody .= "Nyní se můžete přihlásit: {$loginUrl}\n\n";
        $textBody .= "V případě problémů nás kontaktujte na: " . $this->defaultAdminEmail;

        $mail->setBody($textBody, 'text/plain; charset=utf-8');

        $this->mailer->send($mail);
    }

    /**
     * Odešle upozornění adminovi o nové registraci
     * PRO REGISTRACI: vždy používáme defaultAdminEmail, ne tenant-specific
     */
    public function sendAdminNotification(string $username, string $email, string $role): void
    {
        $mail = new Message;

        // PRO REGISTRACI: Vždy použijeme default hodnoty
        $mail->setFrom($this->defaultFromEmail, $this->appName)
            ->addTo($this->defaultAdminEmail)
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
        $htmlBody = $this->createAdminNotificationHtml($username, $email, $roleName, $usersUrl, $this->defaultAdminEmail);
        $mail->setHtmlBody($htmlBody);

        // Textová verze
        $textBody = "Nová registrace v " . $this->appName . "\n\n";
        $textBody .= "Uživatelské jméno: {$username}\n";
        $textBody .= "E-mail: {$email}\n";
        $textBody .= "Role: {$roleName}\n";
        $textBody .= "Čas registrace: " . date('d.m.Y H:i:s') . "\n\n";
        $textBody .= "Přehled uživatelů: {$usersUrl}";

        $mail->setBody($textBody, 'text/plain; charset=utf-8');

        $this->mailer->send($mail);
    }

    /**
     * Odešle email pro resetování hesla
     * PRO RESET HESLA: vždy používáme defaultFromEmail, ne tenant-specific
     */
    public function sendPasswordReset(string $userEmail, string $username, string $resetToken): void
    {
        error_log("EmailService::sendPasswordReset START");
        error_log("  - userEmail: " . $userEmail);
        error_log("  - username: " . $username);
        error_log("  - token: " . substr($resetToken, 0, 10) . "...");
        error_log("  - defaultFromEmail: " . $this->defaultFromEmail);
        error_log("  - appName: " . $this->appName);

        try {
            $mail = new Message;

            // PRO RESET: Vždy použijeme default hodnoty
            error_log("  - Vytvářím Message objekt...");
            $mail->setFrom($this->defaultFromEmail, $this->appName)
                ->addTo($userEmail)
                ->setSubject('Obnovení hesla - ' . $this->appName);
            error_log("  - Message hlavičky nastaveny");

            $resetUrl = $this->linkGenerator->link('Sign:resetPassword', ['token' => $resetToken]);
            error_log("  - Reset URL: " . $resetUrl);

            // HTML verze
            $htmlBody = $this->createPasswordResetHtml($username, $resetUrl, $this->defaultAdminEmail);
            $mail->setHtmlBody($htmlBody);
            error_log("  - HTML body nastaven");

            // Textová verze
            $textBody = "Obnovení hesla pro " . $this->appName . "\n\n";
            $textBody .= "Ahoj {$username},\n\n";
            $textBody .= "Někdo požádal o obnovení hesla pro váš účet.\n";
            $textBody .= "Pokud to nebyli vy, tento email ignorujte.\n\n";
            $textBody .= "Pro obnovení hesla klikněte na následující odkaz:\n";
            $textBody .= "{$resetUrl}\n\n";
            $textBody .= "Odkaz je platný po dobu 24 hodin.\n\n";
            $textBody .= "V případě problémů nás kontaktujte na: " . $this->defaultAdminEmail;

            $mail->setBody($textBody, 'text/plain; charset=utf-8');
            error_log("  - Text body nastaven");

            error_log("  - Volám mailer->send()...");
            $this->mailer->send($mail);
            error_log("EmailService::sendPasswordReset ÚSPĚCH");
        } catch (\Exception $e) {
            error_log("EmailService::sendPasswordReset CHYBA: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Test metoda pro ověření funkčnosti emailové služby
     */
    public function sendTestEmail(string $email): void
    {
        $mail = new Message;

        // OPRAVENO: Místo $this->fromEmail a $this->appName používáme helper metody
        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($email)
            ->setSubject('Test email - ' . $this->getFromName());

        // HTML verze
        $htmlBody = $this->createTestEmailHtml();
        $mail->setHtmlBody($htmlBody);

        // OPRAVENO: Místo $this->appName používáme $this->getFromName()
        $textBody = "Testovací email ze systému " . $this->getFromName() . ".\n\n";
        $textBody .= "Pokud tento email vidíte, emailová služba funguje správně.\n\n";
        $textBody .= "Čas odeslání: " . date('d.m.Y H:i:s');

        $mail->setBody($textBody, 'text/plain; charset=utf-8');

        $this->mailer->send($mail);
    }

    /**
     * Vytvoří HTML pro potvrzení registrace
     */
    private function createRegistrationConfirmationHtml(string $username, string $roleName, string $loginUrl, string $adminEmail): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda místo property

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
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Vítejte v {$appName}!</h1>
                </div>
                <div class='content'>
                    <h2>Registrace byla úspěšná</h2>
                    <p>Váš účet s uživatelským jménem <strong>{$username}</strong> byl úspěšně vytvořen.</p>
                    <p><strong>Role:</strong> {$roleName}</p>
                    
                    <p>Nyní se můžete přihlásit a začít používat systém:</p>
                    <a href='{$loginUrl}' class='btn'>Přihlásit se</a>
                    
                    <p>V případě problémů nás kontaktujte na: <a href='mailto:{$adminEmail}'>{$adminEmail}</a></p>
                </div>
                <div class='footer'>
                    <p>Tento email byl automaticky vygenerován systémem {$appName}</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Vytvoří HTML pro admin notifikaci
     */
    private function createAdminNotificationHtml(string $username, string $email, string $roleName, string $usersUrl, string $adminEmail): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda místo property

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
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Nová registrace</h1>
                </div>
                <div class='content'>
                    <p>V systému {$appName} se zaregistroval nový uživatel:</p>
                    
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
    private function createPasswordResetHtml(string $username, string $resetUrl, string $adminEmail): string
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
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
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
                    <p>V případě problémů nás kontaktujte na: <a href='mailto:{$adminEmail}'>{$adminEmail}</a></p>
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
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda místo property

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
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
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
                    <p>Tento testovací email ze systému <strong>{$appName}</strong> potvrzuje, že odesílání emailů funguje.</p>
                    <p><small>Čas odeslání: " . date('d.m.Y H:i:s') . "</small></p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * ===== ROZŠÍŘENÍ PRO MODULY =====
     * Obecná metoda pro odesílání emailů s podporou šablon pro moduly
     */

    /**
     * Odešle obecný email podle typu a šablony
     * @param string $type Typ emailu (invoice, reminder, notification, atd.)
     * @param string $userEmail Příjemce
     * @param array $data Data pro šablonu
     * @param array $options Dodatečné možnosti (attachments, priority, atd.)
     */
    public function sendModuleEmail(string $type, string $userEmail, array $data = [], array $options = []): void
    {
        $mail = new Message;

        // OPRAVENO: Použití helper metod místo starých property
        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($userEmail);

        // Dynamické nastavení předmětu podle typu
        $subject = $this->getSubjectByType($type, $data);
        $mail->setSubject($subject);

        // Priorita emailu (pro upomínky, urgentní notifikace)
        if (isset($options['priority'])) {
            $mail->setPriority($options['priority']);
        }

        // HTML a textová verze podle typu
        $htmlBody = $this->createEmailBodyByType($type, $data, 'html');
        $textBody = $this->createEmailBodyByType($type, $data, 'text');

        $mail->setHtmlBody($htmlBody);
        $mail->setBody($textBody, 'text/plain; charset=utf-8');

        // Přílohy (pro faktury, dokumenty)
        if (isset($options['attachments']) && is_array($options['attachments'])) {
            foreach ($options['attachments'] as $attachment) {
                if (isset($attachment['file']) && isset($attachment['name'])) {
                    $mail->addAttachment($attachment['file'], $attachment['name']);
                }
            }
        }

        // GDPR COMPLIANT: Bez automatických kopií - pouze na explicitní žádost
        // Kopie se nebudou odesílat automaticky kvůli GDPR

        $this->mailer->send($mail);
    }

    /**
     * Získá předmět emailu podle typu
     */
    private function getSubjectByType(string $type, array $data): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Použití helper metody

        $subjects = [
            // Stávající typy
            'registration' => 'Vítejte v ' . $appName . ' - registrace byla úspěšná',
            'password_reset' => 'Obnovení hesla - ' . $appName,
            'admin_notification' => 'Nová registrace v ' . $appName,
            'test' => 'Test email - ' . $appName,

            // Budoucí moduly - faktury
            'invoice_created' => 'Nová faktura #{invoice_number} - ' . $appName,
            'invoice_sent' => 'Faktura #{invoice_number} byla odeslána - ' . $appName,
            'invoice_paid' => 'Faktura #{invoice_number} byla zaplacena - ' . $appName,
            'invoice_overdue' => 'Faktura #{invoice_number} po splatnosti - ' . $appName,

            // Budoucí moduly - upomínky  
            'reminder_first' => 'Připomínka splatnosti faktury #{invoice_number}',
            'reminder_second' => 'Druhá upomínka - faktura #{invoice_number}',
            'reminder_final' => 'Konečná upomínka - faktura #{invoice_number}',

            // Budoucí moduly - systémové notifikace
            'system_maintenance' => 'Plánovaná údržba systému - ' . $appName,
            'backup_completed' => 'Zálohování dokončeno - ' . $appName,
            'security_alert' => 'Bezpečnostní upozornění - ' . $appName,

            // Budoucí moduly - klientské notifikace
            'client_welcome' => 'Vítejte jako náš nový klient - ' . $appName,
            'client_statement' => 'Měsíční výkaz - ' . $appName,
        ];

        $subject = $subjects[$type] ?? ('Systémový email - ' . $appName);

        // Nahrazení placeholderů v předmětu
        foreach ($data as $key => $value) {
            $subject = str_replace('{' . $key . '}', (string) $value, $subject);
        }

        return $subject;
    }

    /**
     * Vytvoří obsah emailu podle typu a formátu
     */
    private function createEmailBodyByType(string $type, array $data, string $format = 'html'): string
    {
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        switch ($type) {
            // Stávající typy
            case 'registration':
                return $format === 'html'
                    ? $this->createRegistrationConfirmationHtml($data['username'] ?? '', $data['role_name'] ?? '', $data['login_url'] ?? '', $adminEmail)
                    : $this->createRegistrationConfirmationText($data['username'] ?? '', $data['role_name'] ?? '', $data['login_url'] ?? '');

            case 'password_reset':
                return $format === 'html'
                    ? $this->createPasswordResetHtml($data['username'] ?? '', $data['reset_url'] ?? '', $adminEmail)
                    : $this->createPasswordResetText($data['username'] ?? '', $data['reset_url'] ?? '');

            case 'admin_notification':
                return $format === 'html'
                    ? $this->createAdminNotificationHtml($data['username'] ?? '', $data['email'] ?? '', $data['role_name'] ?? '', $data['users_url'] ?? '', $adminEmail)
                    : $this->createAdminNotificationText($data['username'] ?? '', $data['email'] ?? '', $data['role_name'] ?? '', $data['users_url'] ?? '');

                // Budoucí moduly - ukázka struktury pro faktury
            case 'invoice_created':
            case 'invoice_sent':
            case 'invoice_paid':
            case 'invoice_overdue':
                return $format === 'html'
                    ? $this->createInvoiceEmailHtml($type, $data)
                    : $this->createInvoiceEmailText($type, $data);

                // Budoucí moduly - upomínky
            case 'reminder_first':
            case 'reminder_second':
            case 'reminder_final':
                return $format === 'html'
                    ? $this->createReminderEmailHtml($type, $data)
                    : $this->createReminderEmailText($type, $data);

                // Výchozí šablona pro neznámé typy
            default:
                return $format === 'html'
                    ? $this->createGenericEmailHtml($type, $data)
                    : $this->createGenericEmailText($type, $data);
        }
    }

    /**
     * Nová textová verze pro registraci
     */
    private function createRegistrationConfirmationText(string $username, string $roleName, string $loginUrl): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        $textBody = "Vítejte v {$appName}!\n\n";
        $textBody .= "Váš účet s uživatelským jménem '{$username}' byl úspěšně vytvořen.\n";
        $textBody .= "Role: {$roleName}\n\n";
        $textBody .= "Nyní se můžete přihlásit: {$loginUrl}\n\n";
        $textBody .= "V případě problémů nás kontaktujte na: {$adminEmail}";
        return $textBody;
    }

    /**
     * Nová textová verze pro reset hesla
     */
    private function createPasswordResetText(string $username, string $resetUrl): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        $textBody = "Obnovení hesla pro {$appName}\n\n";
        $textBody .= "Ahoj {$username},\n\n";
        $textBody .= "Někdo požádal o obnovení hesla pro váš účet.\n";
        $textBody .= "Pokud to nebyli vy, tento email ignorujte.\n\n";
        $textBody .= "Pro obnovení hesla klikněte na následující odkaz:\n";
        $textBody .= "{$resetUrl}\n\n";
        $textBody .= "Odkaz je platný po dobu 24 hodin.\n\n";
        $textBody .= "V případě problémů nás kontaktujte na: {$adminEmail}";
        return $textBody;
    }

    /**
     * Nová textová verze pro admin notifikaci  
     */
    private function createAdminNotificationText(string $username, string $email, string $roleName, string $usersUrl): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda

        $textBody = "Nová registrace v {$appName}\n\n";
        $textBody .= "Uživatelské jméno: {$username}\n";
        $textBody .= "E-mail: {$email}\n";
        $textBody .= "Role: {$roleName}\n";
        $textBody .= "Čas registrace: " . date('d.m.Y H:i:s') . "\n\n";
        $textBody .= "Přehled uživatelů: {$usersUrl}";
        return $textBody;
    }

    /**
     * BUDOUCÍ MODUL: Šablony pro faktury (připraveno pro implementaci)
     */
    private function createInvoiceEmailHtml(string $type, array $data): string
    {
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        // Připraveno pro budoucí implementaci fakturačního modulu
        $invoiceNumber = $data['invoice_number'] ?? 'N/A';
        $clientName = $data['client_name'] ?? 'Zákazník';
        $amount = $data['amount'] ?? '0 Kč';
        $dueDate = $data['due_date'] ?? '';

        $messages = [
            'invoice_created' => "Vaše faktura byla vytvořena",
            'invoice_sent' => "Vaše faktura byla odeslána",
            'invoice_paid' => "Děkujeme za úhradu faktury",
            'invoice_overdue' => "Faktura je po splatnosti"
        ];

        $message = $messages[$type] ?? "Informace o faktuře";

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
                .invoice-info { background: white; padding: 20px; border-left: 4px solid #B1D235; margin: 20px 0; }
                .btn { background: #95B11F; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$message}</h1>
                </div>
                <div class='content'>
                    <p>Vážený {$clientName},</p>
                    
                    <div class='invoice-info'>
                        <p><strong>Číslo faktury:</strong> {$invoiceNumber}</p>
                        <p><strong>Částka:</strong> {$amount}</p>
                        " . ($dueDate ? "<p><strong>Splatnost:</strong> {$dueDate}</p>" : "") . "
                    </div>
                    
                    <p>V případě dotazů nás kontaktujte na: <a href='mailto:{$adminEmail}'>{$adminEmail}</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function createInvoiceEmailText(string $type, array $data): string
    {
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        // Textová verze pro fakturační emaily
        $invoiceNumber = $data['invoice_number'] ?? 'N/A';
        $clientName = $data['client_name'] ?? 'Zákazník';
        $amount = $data['amount'] ?? '0 Kč';

        $messages = [
            'invoice_created' => "Vaše faktura byla vytvořena",
            'invoice_sent' => "Vaše faktura byla odeslána",
            'invoice_paid' => "Děkujeme za úhradu faktury",
            'invoice_overdue' => "Faktura je po splatnosti"
        ];

        $message = $messages[$type] ?? "Informace o faktuře";

        $textBody = "{$message}\n\n";
        $textBody .= "Vážený {$clientName},\n\n";
        $textBody .= "Číslo faktury: {$invoiceNumber}\n";
        $textBody .= "Částka: {$amount}\n\n";
        $textBody .= "V případě dotazů nás kontaktujte na: {$adminEmail}";

        return $textBody;
    }

    /**
     * BUDOUCÍ MODUL: Šablony pro upomínky
     */
    private function createReminderEmailHtml(string $type, array $data): string
    {
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        $reminderTexts = [
            'reminder_first' => 'Připomínáme splatnost faktury',
            'reminder_second' => 'Druhá upomínka - faktura stále není uhrazena',
            'reminder_final' => 'Konečná upomínka - okamžitá úhrada'
        ];

        $reminderColors = [
            'reminder_first' => '#B1D235',
            'reminder_second' => '#ffc107',
            'reminder_final' => '#dc3545'
        ];

        $title = $reminderTexts[$type] ?? 'Upomínka';
        $headerColor = $reminderColors[$type] ?? '#B1D235';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #212529; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: {$headerColor}; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .invoice-info { background: white; padding: 20px; border-left: 4px solid {$headerColor}; margin: 20px 0; }
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                <div class='content'>
                    <p>Vážený zákazníku,</p>
                    <div class='invoice-info'>
                        <p><strong>Faktura:</strong> " . ($data['invoice_number'] ?? 'N/A') . "</p>
                        <p><strong>Částka:</strong> " . ($data['amount'] ?? '0 Kč') . "</p>
                        <p><strong>Původní splatnost:</strong> " . ($data['due_date'] ?? 'N/A') . "</p>
                    </div>
                    <p>Kontakt: <a href='mailto:{$adminEmail}'>{$adminEmail}</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function createReminderEmailText(string $type, array $data): string
    {
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        $reminderTexts = [
            'reminder_first' => 'Připomínáme splatnost faktury',
            'reminder_second' => 'Druhá upomínka - faktura stále není uhrazena',
            'reminder_final' => 'Konečná upomínka - okamžitá úhrada'
        ];

        $title = $reminderTexts[$type] ?? 'Upomínka';

        $textBody = "{$title}\n\n";
        $textBody .= "Vážený zákazníku,\n\n";
        $textBody .= "Faktura: " . ($data['invoice_number'] ?? 'N/A') . "\n";
        $textBody .= "Částka: " . ($data['amount'] ?? '0 Kč') . "\n";
        $textBody .= "Původní splatnost: " . ($data['due_date'] ?? 'N/A') . "\n\n";
        $textBody .= "Kontakt: {$adminEmail}";

        return $textBody;
    }

    /**
     * Obecná šablona pro neznámé typy emailů
     */
    private function createGenericEmailHtml(string $type, array $data): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda
        $title = ucfirst(str_replace('_', ' ', $type));

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
                a { color: #B1D235; }
                a:hover { color: #95B11F; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$title}</h1>
                </div>
                <div class='content'>
                    <p>Systémová zpráva z {$appName}</p>
                    <p>Typ: {$type}</p>
                    <p>Kontakt: <a href='mailto:{$adminEmail}'>{$adminEmail}</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    private function createGenericEmailText(string $type, array $data): string
    {
        $appName = $this->getFromName(); // OPRAVENO: Helper metoda  
        $adminEmail = $this->getAdminEmail(); // OPRAVENO: Helper metoda

        return "Systémová zpráva z {$appName}\n\nTyp: {$type}\n\nKontakt: {$adminEmail}";
    }

    /**
     * Odešle fakturu emailem klientovi
     * @param object $invoice Faktura
     * @param object $client Klient (s dešifrovanými daty)
     * @param object $company Firma (s dešifrovanými daty)
     * @param string $pdfPath Cesta k PDF souboru faktury
     */
    public function sendInvoiceEmail($invoice, $client, $company, string $pdfPath): void
    {
        // Kontrola, zda klient má email
        if (empty($client->email)) {
            throw new \Exception('Klient nemá zadaný email.');
        }

        // Kontrola, zda firma má email
        if (empty($company->email)) {
            throw new \Exception('Firma nemá zadaný email pro odesílání faktur.');
        }

        $mail = new Message;

        // Email odesílatele = email firmy (již dešifrovaný)
        // Email příjemce = email klienta (již dešifrovaný)
        $mail->setFrom($company->email, $company->name)
            ->addTo($client->email, $client->name);

        // Předmět emailu
        $subject = 'Faktura ' . $invoice->number . ' - ' . $company->name;
        $mail->setSubject($subject);

        // HTML tělo emailu
        $htmlBody = $this->createSentInvoiceHtmlBody($invoice, $client, $company);
        $mail->setHtmlBody($htmlBody);

        // Textová verze emailu
        $textBody = $this->createSentInvoiceTextBody($invoice, $client, $company);
        $mail->setBody($textBody, 'text/plain; charset=utf-8');

        // Příloha - PDF faktura
        if (file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath, 'faktura-' . $invoice->number . '.pdf');
        }

        // Odeslání emailu
        $this->mailer->send($mail);
    }

    /**
     * Vytvoří HTML tělo emailu pro odeslanou fakturu
     */
    private function createSentInvoiceHtmlBody($invoice, $client, $company): string
    {
        $invoiceNumber = $invoice->number;
        $clientName = $client->name;
        $amount = number_format($invoice->total, 0, ',', ' ') . ' Kč';
        $dueDate = $invoice->due_date->format('d.m.Y');
        $issueDate = $invoice->issue_date->format('d.m.Y');

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
            .invoice-info { background: white; padding: 20px; border-left: 4px solid #B1D235; margin: 20px 0; }
            .info-row { margin: 10px 0; }
            .label { font-weight: bold; color: #6c757d; }
            .footer { text-align: center; color: #6c757d; font-size: 12px; margin-top: 30px; }
            a { color: #B1D235; text-decoration: none; }
            a:hover { color: #95B11F; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Faktura {$invoiceNumber}</h1>
            </div>
            <div class='content'>
                <p>Vážený/á {$clientName},</p>
                
                <p>zasíláme Vám fakturu za poskytnuté služby.</p>
                
                <div class='invoice-info'>
                    <div class='info-row'>
                        <span class='label'>Číslo faktury:</span> {$invoiceNumber}
                    </div>
                    <div class='info-row'>
                        <span class='label'>Datum vystavení:</span> {$issueDate}
                    </div>
                    <div class='info-row'>
                        <span class='label'>Datum splatnosti:</span> {$dueDate}
                    </div>
                    <div class='info-row'>
                        <span class='label'>Částka k úhradě:</span> <strong>{$amount}</strong>
                    </div>
                </div>
                
                <p>Faktura je přiložena jako PDF příloha v tomto emailu.</p>
                
                <p>V případě jakýchkoliv dotazů nás neváhejte kontaktovat.</p>
                
                <p>S pozdravem,<br>
                {$company->name}</p>
                
                <div class='footer'>
                    <p>{$company->name}<br>
                    {$company->address}, {$company->zip} {$company->city}<br>
                    Email: <a href='mailto:{$company->email}'>{$company->email}</a> | 
                    Tel: {$company->phone}</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
    }

    /**
     * Vytvoří textové tělo emailu pro odeslanou fakturu
     */
    private function createSentInvoiceTextBody($invoice, $client, $company): string
    {
        $invoiceNumber = $invoice->number;
        $clientName = $client->name;
        $amount = number_format($invoice->total, 0, ',', ' ') . ' Kč';
        $dueDate = $invoice->due_date->format('d.m.Y');
        $issueDate = $invoice->issue_date->format('d.m.Y');

        $textBody = "FAKTURA {$invoiceNumber}\n\n";
        $textBody .= "Vážený/á {$clientName},\n\n";
        $textBody .= "zasíláme Vám fakturu za poskytnuté služby.\n\n";
        $textBody .= "ÚDAJE O FAKTUŘE:\n";
        $textBody .= "Číslo faktury: {$invoiceNumber}\n";
        $textBody .= "Datum vystavení: {$issueDate}\n";
        $textBody .= "Datum splatnosti: {$dueDate}\n";
        $textBody .= "Částka k úhradě: {$amount}\n\n";
        $textBody .= "Faktura je přiložena jako PDF příloha v tomto emailu.\n\n";
        $textBody .= "V případě jakýchkoliv dotazů nás neváhejte kontaktovat.\n\n";
        $textBody .= "S pozdravem,\n";
        $textBody .= "{$company->name}\n\n";
        $textBody .= "---\n";
        $textBody .= "{$company->name}\n";
        $textBody .= "{$company->address}, {$company->zip} {$company->city}\n";
        $textBody .= "Email: {$company->email} | Tel: {$company->phone}";

        return $textBody;
    }
}
