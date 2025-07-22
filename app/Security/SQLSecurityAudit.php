<?php

namespace App\Security;

use Nette;

/**
 * SQL Security Audit Tool - Webová verze
 * Kontrola raw SQL dotazů v celém projektu
 * 
 * Barvy projektu: primární #B1D235, sekundární #95B11F, šedá #6c757d, černá #212529
 */
class SQLSecurityAudit
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    /** @var string */
    private $projectRoot;

    /** @var array Vzory pro vyhledání potenciálně nebezpečných SQL dotazů */
    private $dangerousPatterns = [
        // Přímé vkládání proměnných do SQL
        '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*\.\s*["\']/',
        '/["\'][^"\']*\$[a-zA-Z_][a-zA-Z0-9_]*[^"\']*["\']/',
        
        // Konkatenace stringů v SQL
        '/["\'][^"\']*["\'][^;]*\.[^;]*["\'][^"\']*["\']/',
        
        // WHERE bez parametrů
        '/WHERE\s+[^?]*=\s*["\'][^"\']*\$/',
        '/WHERE\s+[^?]*LIKE\s*["\'][^"\']*\$/',
        
        // INSERT/UPDATE bez parametrů
        '/INSERT\s+INTO\s+[^?]*VALUES\s*\([^?]*\$/',
        '/UPDATE\s+[^?]*SET\s+[^?]*=\s*["\'][^"\']*\$/',
    ];

    /** @var array Soubory a adresáře k prohledání */
    private $searchPaths = [
        'app/Model',
        'app/Presentation',
        'Modules'
    ];

    /** @var array Přípony souborů k prohledání */
    private $fileExtensions = ['php'];

    /** @var array Výsledky auditu */
    private $auditResults = [];

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
        // Automatické určení project root
        $this->projectRoot = realpath(__DIR__ . '/../../');
    }

    /**
     * Spustí kompletní audit SQL bezpečnosti
     */
    public function runFullAudit(): array
    {
        $this->auditResults = [
            'timestamp' => new \DateTime(),
            'files_scanned' => 0,
            'sql_queries_found' => 0,
            'potential_issues' => [],
            'safe_queries' => [],
            'summary' => []
        ];

        // Prohledání všech souborů
        foreach ($this->searchPaths as $path) {
            $fullPath = $this->projectRoot . DIRECTORY_SEPARATOR . $path;
            if (is_dir($fullPath)) {
                $this->scanDirectory($fullPath);
            }
        }

        // Vytvoření shrnutí
        $this->generateSummary();

        return $this->auditResults;
    }

    /**
     * Prohledá adresář rekurzivně
     */
    private function scanDirectory(string $directory): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), $this->fileExtensions)) {
                $this->scanFile($file->getPathname());
            }
        }
    }

    /**
     * Prohledá jednotlivý soubor
     */
    private function scanFile(string $filePath): void
    {
        $this->auditResults['files_scanned']++;
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $lines = explode("\n", $content);
        $relativePath = str_replace($this->projectRoot, '', $filePath);

        // Hledání database->query() volání
        $this->findDatabaseQueries($content, $lines, $relativePath);

        // Hledání potenciálně nebezpečných vzorů
        $this->findDangerousPatterns($content, $lines, $relativePath);
    }

    /**
     * Najde všechna volání database->query()
     */
    private function findDatabaseQueries(string $content, array $lines, string $filePath): void
    {
        $pattern = '/\$[a-zA-Z_][a-zA-Z0-9_]*->query\s*\([^)]+\)/';
        
        if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $this->auditResults['sql_queries_found']++;
                
                $lineNumber = $this->getLineNumber($content, $match[1]);
                $queryText = $match[0];
                
                // Analýza bezpečnosti dotazu
                $isSafe = $this->analyzeQuerySafety($queryText);
                
                $queryInfo = [
                    'file' => $filePath,
                    'line' => $lineNumber,
                    'query' => trim($queryText),
                    'context' => trim($lines[$lineNumber - 1] ?? ''),
                    'safety_score' => $isSafe['score'],
                    'issues' => $isSafe['issues'],
                    'recommendations' => $isSafe['recommendations']
                ];

                if ($isSafe['score'] < 8) {
                    $this->auditResults['potential_issues'][] = $queryInfo;
                } else {
                    $this->auditResults['safe_queries'][] = $queryInfo;
                }
            }
        }
    }

    /**
     * Najde potenciálně nebezpečné vzory
     */
    private function findDangerousPatterns(string $content, array $lines, string $filePath): void
    {
        foreach ($this->dangerousPatterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $lineNumber = $this->getLineNumber($content, $match[1]);
                    
                    $this->auditResults['potential_issues'][] = [
                        'file' => $filePath,
                        'line' => $lineNumber,
                        'pattern' => $pattern,
                        'matched_text' => $match[0],
                        'context' => trim($lines[$lineNumber - 1] ?? ''),
                        'type' => 'dangerous_pattern',
                        'severity' => 'high',
                        'safety_score' => 2,
                        'issues' => ['Nebezpečný vzor SQL injection'],
                        'recommendations' => ['Použijte parametrizované dotazy místo přímé konkatenace']
                    ];
                }
            }
        }
    }

    /**
     * Analyzuje bezpečnost SQL dotazu
     */
    private function analyzeQuerySafety(string $query): array
    {
        $score = 10; // Maximální skóre
        $issues = [];
        $recommendations = [];

        // Kontrola parametrizovaných dotazů (otazníky)
        $parameterCount = substr_count($query, '?');
        if ($parameterCount === 0 && $this->containsVariables($query)) {
            $score -= 5;
            $issues[] = 'Dotaz nepoužívá parametrizované dotazy';
            $recommendations[] = 'Použijte ? parametry místo přímé konkatenace';
        }

        // Kontrola nebezpečných funkcí
        if (preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*/', $query)) {
            $score -= 3;
            $issues[] = 'Dotaz obsahuje přímé proměnné';
            $recommendations[] = 'Nahraďte proměnné ? parametry';
        }

        // Kontrola konkatenace
        if (preg_match('/\.\s*["\']|\'\s*\.|\"\s*\./', $query)) {
            $score -= 4;
            $issues[] = 'Dotaz používá konkatenaci stringů';
            $recommendations[] = 'Použijte parametrizované dotazy pro všechny proměnné hodnoty';
        }

        // Pozitivní body za dobré praktiky
        if ($parameterCount > 0) {
            $issues[] = 'Dotaz používá parametrizované dotazy ✓';
        }

        if (preg_match('/WHERE|ORDER BY|LIMIT/i', $query) && $parameterCount > 0) {
            $score += 1;
            $issues[] = 'Správné použití parametrů ve WHERE/ORDER BY/LIMIT ✓';
        }

        return [
            'score' => max(0, min(10, $score)),
            'issues' => $issues,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Kontroluje, zda dotaz obsahuje proměnné
     */
    private function containsVariables(string $query): bool
    {
        return preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*/', $query) === 1;
    }

    /**
     * Získá číslo řádku pro danou pozici v textu
     */
    private function getLineNumber(string $content, int $offset): int
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }

    /**
     * Generuje shrnutí auditu
     */
    private function generateSummary(): void
    {
        $totalQueries = $this->auditResults['sql_queries_found'];
        $issueCount = count($this->auditResults['potential_issues']);
        $safeCount = count($this->auditResults['safe_queries']);

        $this->auditResults['summary'] = [
            'files_scanned' => $this->auditResults['files_scanned'],
            'total_queries' => $totalQueries,
            'safe_queries' => $safeCount,
            'potential_issues' => $issueCount,
            'safety_percentage' => $totalQueries > 0 ? round(($safeCount / $totalQueries) * 100, 2) : 100,
            'overall_status' => $this->getOverallStatus($issueCount, $totalQueries),
            'priority_issues' => $this->getPriorityIssues(),
            'recommendations' => $this->getGlobalRecommendations()
        ];
    }

    /**
     * Určí celkový status bezpečnosti
     */
    private function getOverallStatus(int $issueCount, int $totalQueries): string
    {
        if ($issueCount === 0) {
            return 'EXCELLENT';
        } elseif ($issueCount < 3 || ($totalQueries > 0 && $issueCount / $totalQueries < 0.1)) {
            return 'GOOD';
        } elseif ($issueCount < 10 || ($totalQueries > 0 && $issueCount / $totalQueries < 0.3)) {
            return 'NEEDS_ATTENTION';
        } else {
            return 'CRITICAL';
        }
    }

    /**
     * Získá prioritní problémy
     */
    private function getPriorityIssues(): array
    {
        $priorityIssues = [];
        
        foreach ($this->auditResults['potential_issues'] as $issue) {
            if (isset($issue['safety_score']) && $issue['safety_score'] < 5) {
                $priorityIssues[] = $issue;
            }
        }

        return array_slice($priorityIssues, 0, 10); // Maximálně 10 nejkritičtějších
    }

    /**
     * Získá globální doporučení
     */
    private function getGlobalRecommendations(): array
    {
        $recommendations = [
            'Vždy použijte parametrizované dotazy s ? pro všechny uživatelské vstupy',
            'Nikdy nekoncatenujte proměnné přímo do SQL stringů',
            'Využívajte Nette Database Explorer metody (table(), where()) které jsou bezpečné',
            'Pravidelně provádějte SQL injection testy',
            'Implementujte code review pro všechny databázové dotazy'
        ];

        // Přidání specifických doporučení na základě nalezených problémů
        $issueTypes = array_column($this->auditResults['potential_issues'], 'type');
        
        if (in_array('dangerous_pattern', $issueTypes)) {
            $recommendations[] = 'URGENT: Nalezeny nebezpečné vzory - okamžitě opravte konkatenaci v SQL';
        }

        return $recommendations;
    }

    /**
     * Vrátí výsledky ve formátu pro webové zobrazení
     */
    public function getWebResults(): array
    {
        return $this->auditResults;
    }

    /**
     * Exportuje pouze kritické problémy
     */
    public function getCriticalIssues(): array
    {
        $critical = [];
        foreach ($this->auditResults['potential_issues'] as $issue) {
            if (isset($issue['safety_score']) && $issue['safety_score'] < 3) {
                $critical[] = $issue;
            }
        }
        return $critical;
    }

    /**
     * Vrátí pouze bezpečné dotazy pro ukázku
     */
    public function getSafeExamples(): array
    {
        return array_slice($this->auditResults['safe_queries'], 0, 5);
    }
}