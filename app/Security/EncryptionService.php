<?php

declare(strict_types=1);

namespace App\Security;

use Nette;
use Tracy\Debugger;

/**
 * Služba pro šifrování citlivých dat v databázi
 * Používá AES-256-GCM pro maximální bezpečnost
 */
class EncryptionService
{
    use Nette\SmartObject;

    private const CIPHER_METHOD = 'aes-256-gcm';
    private const IV_LENGTH = 12; // Pro GCM doporučeno 12 bytů
    private const TAG_LENGTH = 16; // Délka autentifikačního tagu
    
    /** @var string */
    private $encryptionKey;
    
    /** @var bool */
    private $encryptionEnabled;

    public function __construct()
    {
        // Načtení šifrovacího klíče z environment variable
        $this->encryptionKey = $_ENV['ENCRYPTION_KEY'] ?? '';
        
        // Ověření, zda je šifrování povoleno
        $this->encryptionEnabled = !empty($this->encryptionKey) && 
                                  strlen($this->encryptionKey) >= 32;
        
        if (!$this->encryptionEnabled) {
            Debugger::log('ENCRYPTION: Šifrování není povoleno - chybí nebo je příliš krátký ENCRYPTION_KEY', Debugger::WARNING);
        }
        
        // Ověření dostupnosti OpenSSL
        if (!extension_loaded('openssl')) {
            throw new \Exception('OpenSSL extension je vyžadována pro šifrování');
        }
        
        // Ověření podpory AES-256-GCM
        if (!in_array(self::CIPHER_METHOD, openssl_get_cipher_methods())) {
            throw new \Exception('AES-256-GCM šifrování není podporováno');
        }
    }

    /**
     * Šifruje citlivá data
     * @param string $plaintext Data k zašifrování
     * @return string|null Zašifrovaná data nebo null při chybě
     */
    public function encrypt(string $plaintext): ?string
    {
        // Pokud je šifrování vypnuto, vrátíme původní data
        if (!$this->encryptionEnabled) {
            return $plaintext;
        }
        
        // Prázdný string nebudeme šifrovat
        if (empty($plaintext)) {
            return $plaintext;
        }

        try {
            // Generování náhodného IV (inicializační vektor)
            $iv = openssl_random_pseudo_bytes(self::IV_LENGTH);
            
            // Šifrování s autentifikačním tagem
            $tag = '';
            $encrypted = openssl_encrypt(
                $plaintext,
                self::CIPHER_METHOD,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($encrypted === false) {
                Debugger::log('ENCRYPTION: Chyba při šifrování dat', Debugger::ERROR);
                return null;
            }
            
            // Kombinace IV + tag + šifrovaná data do jednoho stringu
            // Format: base64(iv + tag + encrypted_data)
            $result = base64_encode($iv . $tag . $encrypted);
            
            return $result;
            
        } catch (\Exception $e) {
            Debugger::log('ENCRYPTION: Exception při šifrování: ' . $e->getMessage(), Debugger::ERROR);
            return null;
        }
    }

    /**
     * Dešifruje citlivá data
     * @param string $encryptedData Šifrovaná data
     * @return string|null Dešifrovaná data nebo null při chybě
     */
    public function decrypt(string $encryptedData): ?string
    {
        // Pokud je šifrování vypnuto, vrátíme původní data
        if (!$this->encryptionEnabled) {
            return $encryptedData;
        }
        
        // Prázdný string nebudeme dešifrovat
        if (empty($encryptedData)) {
            return $encryptedData;
        }

        try {
            // Dekódování z base64
            $data = base64_decode($encryptedData);
            
            if ($data === false) {
                // Pokud dekódování selže, možná jsou data již v plain textu
                // (backward compatibility pro existující data)
                return $encryptedData;
            }
            
            // Minimální délka: IV + tag + nějaká data
            $minLength = self::IV_LENGTH + self::TAG_LENGTH + 1;
            if (strlen($data) < $minLength) {
                // Pravděpodobně plain text data
                return $encryptedData;
            }
            
            // Extrakce IV, tagu a šifrovaných dat
            $iv = substr($data, 0, self::IV_LENGTH);
            $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
            $encrypted = substr($data, self::IV_LENGTH + self::TAG_LENGTH);
            
            // Dešifrování
            $decrypted = openssl_decrypt(
                $encrypted,
                self::CIPHER_METHOD,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($decrypted === false) {
                Debugger::log('ENCRYPTION: Chyba při dešifrování dat - možná jsou data již v plain textu', Debugger::WARNING);
                // Vrátíme původní data (backward compatibility)
                return $encryptedData;
            }
            
            return $decrypted;
            
        } catch (\Exception $e) {
            Debugger::log('ENCRYPTION: Exception při dešifrování: ' . $e->getMessage(), Debugger::ERROR);
            // Vrátíme původní data (backward compatibility)
            return $encryptedData;
        }
    }

    /**
     * Ověří, zda je šifrování zapnuto
     * @return bool
     */
    public function isEncryptionEnabled(): bool
    {
        return $this->encryptionEnabled;
    }

    /**
     * Generuje náhodný šifrovací klíč (pro setup)
     * @return string Base64 kódovaný klíč
     */
    public static function generateEncryptionKey(): string
    {
        $randomBytes = openssl_random_pseudo_bytes(32); // 256 bitů
        return base64_encode($randomBytes);
    }

    /**
     * Ověří, zda jsou data zašifrovaná (heuristic check)
     * @param string $data Data k ověření
     * @return bool True pokud vypadají jako zašifrovaná data
     */
    public function isDataEncrypted(string $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        // Pokud je šifrování vypnuto, data nejsou šifrovaná
        if (!$this->encryptionEnabled) {
            return false;
        }
        
        // Zkusíme dekódovat z base64
        $decoded = base64_decode($data, true);
        
        if ($decoded === false) {
            return false; // Není base64
        }
        
        // Kontrola minimální délky pro šifrovaná data
        $minLength = self::IV_LENGTH + self::TAG_LENGTH + 1;
        
        return strlen($decoded) >= $minLength;
    }

    /**
     * Hromadné šifrování více hodnot
     * @param array $data Asociativní pole dat k zašifrování
     * @param array $fieldsToEncrypt Pole názvů klíčů, které se mají šifrovat
     * @return array Data s zašifrovanými hodnotami
     */
    public function encryptFields(array $data, array $fieldsToEncrypt): array
    {
        $result = $data;
        
        foreach ($fieldsToEncrypt as $field) {
            if (isset($result[$field]) && is_string($result[$field])) {
                $encrypted = $this->encrypt($result[$field]);
                if ($encrypted !== null) {
                    $result[$field] = $encrypted;
                }
            }
        }
        
        return $result;
    }

    /**
     * Hromadné dešifrování více hodnot
     * @param array $data Asociativní pole dat k dešifrování
     * @param array $fieldsToDecrypt Pole názvů klíčů, které se mají dešifrovat
     * @return array Data s dešifrovanými hodnotami
     */
    public function decryptFields(array $data, array $fieldsToDecrypt): array
    {
        $result = $data;
        
        foreach ($fieldsToDecrypt as $field) {
            if (isset($result[$field]) && is_string($result[$field])) {
                $decrypted = $this->decrypt($result[$field]);
                if ($decrypted !== null) {
                    $result[$field] = $decrypted;
                }
            }
        }
        
        return $result;
    }
}