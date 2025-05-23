<?php

declare(strict_types=1);

namespace App\Security;

use Nette;
use Tracy\ILogger;

/**
 * Třída pro logování bezpečnostních událostí
 */
class SecurityLogger
{
    /** @var ILogger */
    private $logger;
    
    /** @var Nette\Http\Request */
    private $httpRequest;
    
    /** @var string Název souboru pro logování bezpečnostních událostí */
    private $securityLogFile = 'security.log';
    
    public function __construct(ILogger $logger, Nette\Http\Request $httpRequest)
    {
        $this->logger = $logger;
        $this->httpRequest = $httpRequest;
    }
    
    /**
     * Zaloguje událost přihlášení
     */
    public function logLogin(int $userId, string $username): void
    {
        $this->logSecurityEvent('login', "Uživatel $username (ID: $userId) se přihlásil");
    }
    
    /**
     * Zaloguje událost odhlášení
     */
    public function logLogout(int $userId, string $username): void
    {
        $this->logSecurityEvent('logout', "Uživatel $username (ID: $userId) se odhlásil");
    }
    
    /**
     * Zaloguje neúspěšný pokus o přihlášení
     */
    public function logFailedLogin(string $username, string $reason = 'nesprávné heslo'): void
    {
        $this->logSecurityEvent('failed_login', "Neúspěšný pokus o přihlášení pro uživatele '$username': $reason");
    }
    
    /**
     * Zaloguje změnu hesla
     */
    public function logPasswordChange(int $userId, string $username, bool $byAdmin = false): void
    {
        $byWhom = $byAdmin ? 'administrátorem' : 'uživatelem';
        $this->logSecurityEvent('password_change', "Heslo pro uživatele $username (ID: $userId) bylo změněno $byWhom");
    }
    
    /**
     * Zaloguje změnu role
     */
    public function logRoleChange(int $userId, string $username, string $oldRole, string $newRole, int $adminId, string $adminName): void
    {
        $this->logSecurityEvent(
            'role_change', 
            "Role uživatele $username (ID: $userId) byla změněna z '$oldRole' na '$newRole' administrátorem $adminName (ID: $adminId)"
        );
    }
    
    /**
     * Zaloguje vytvoření uživatele
     */
    public function logUserCreation(int $userId, string $username, string $role, ?int $adminId = null, ?string $adminName = null): void
    {
        $byWhom = $adminId ? "administrátorem $adminName (ID: $adminId)" : "samoregistrací";
        $this->logSecurityEvent('user_creation', "Uživatel $username (ID: $userId) s rolí '$role' byl vytvořen $byWhom");
    }
    
    /**
     * Zaloguje smazání uživatele
     */
    public function logUserDeletion(int $userId, string $username, int $adminId, string $adminName): void
    {
        $this->logSecurityEvent(
            'user_deletion', 
            "Uživatel $username (ID: $userId) byl smazán administrátorem $adminName (ID: $adminId)"
        );
    }
    
    /**
     * Zaloguje zablokování účtu
     */
    public function logAccountLockout(int $userId, string $username): void
    {
        $this->logSecurityEvent('account_lockout', "Účet uživatele $username (ID: $userId) byl dočasně zablokován kvůli příliš mnoha neúspěšným pokusům o přihlášení");
    }
    
    /**
     * Zaloguje pokus o přístup k neoprávněné akci
     */
    public function logUnauthorizedAccess(string $resource, ?int $userId = null, ?string $username = null): void
    {
        $user = $userId ? "uživatelem $username (ID: $userId)" : "nepřihlášeným uživatelem";
        $this->logSecurityEvent('unauthorized_access', "Pokus o neoprávněný přístup k '$resource' $user");
    }
    
    /**
     * Zaloguje obecnou bezpečnostní událost
     * 
     * @param string $eventType Typ události (login, logout, failed_login, password_change, role_change, ...)
     * @param string $message Zpráva popisující událost
     */
    public function logSecurityEvent(string $eventType, string $message): void
    {
        $ip = $this->httpRequest->getRemoteAddress();
        $userAgent = $this->httpRequest->getHeader('User-Agent');
        
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [UA: %s] %s",
            date('Y-m-d H:i:s'),
            $eventType,
            $ip,
            $userAgent,
            $message
        );
        
        // Logujeme do speciálního souboru pro bezpečnostní události
        $this->logger->log($logEntry, $this->securityLogFile);
    }
}