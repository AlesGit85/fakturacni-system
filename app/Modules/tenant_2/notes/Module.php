<?php

declare(strict_types=1);

namespace Modules\Notes;

use App\Modules\BaseModule;
use Nette\Database\Explorer;

/**
 * Modul pro správu poznámek
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // Inicializace modulu poznámek
    }
    
    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        // Logika při aktivaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        // Logika při deaktivaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function uninstall(): void
    {
        // Cleanup při odinstalaci modulu
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMenuItems(): array
    {
        return [
            [
                'presenter' => 'ModuleAdmin',
                'action' => 'detail',
                'params' => ['id' => 'notes'],
                'label' => 'Poznámky',
                'icon' => 'bi bi-sticky'
            ]
        ];
    }
    
    /**
     * {@inheritdoc}
     * 
     * Zpracování AJAX požadavků pro poznámky
     */
    public function handleAjaxRequest(string $action, array $parameters = [], array $dependencies = [])
    {
        $this->log("Zpracovávám AJAX akci: $action");
        
        try {
            // Získáme databázi z závislostí
            $database = $this->getDependency($dependencies, Explorer::class);
            
            if (!$database) {
                throw new \Exception('Chybí databázová závislost pro modul Notes');
            }
            
            $this->log("Databáze úspěšně získána");
            
            // Vytvoříme instanci NotesManagera
            $notesManager = $this->getNotesManager($database);
            
            // Zpracujeme akci
            switch ($action) {
                case 'getAllNotes':
                    $this->log("Volám getAllNotes");
                    $userId = $parameters['user_id'] ?? null;
                    $result = $notesManager->getAll($userId);
                    $this->log("getAllNotes výsledek: " . count($result) . " poznámek");
                    return $result;

                case 'addNote':
                    $this->log("Volám addNote");
                    $noteData = [
                        'title' => $parameters['title'] ?? '',
                        'content' => $parameters['content'] ?? '',
                        'category' => $parameters['category'] ?? null,
                        'priority' => $parameters['priority'] ?? 'normal',
                        'tags' => $parameters['tags'] ?? null,
                        'user_id' => $parameters['user_id'] ?? null
                    ];
                    
                    if (empty($noteData['title']) || empty($noteData['content'])) {
                        throw new \Exception('Název a obsah poznámky jsou povinné');
                    }
                    
                    $noteId = $notesManager->add($noteData);
                    $this->log("addNote úspěšně - ID: $noteId");
                    
                    // Vrátíme kompletní poznámku
                    return $notesManager->getById($noteId);

                case 'updateNote':
                    $this->log("Volám updateNote");
                    $noteId = (int)($parameters['id'] ?? 0);
                    $noteData = [
                        'title' => $parameters['title'] ?? '',
                        'content' => $parameters['content'] ?? '',
                        'category' => $parameters['category'] ?? null,
                        'priority' => $parameters['priority'] ?? 'normal',
                        'tags' => $parameters['tags'] ?? null
                    ];
                    
                    if ($noteId <= 0) {
                        throw new \Exception('Neplatné ID poznámky');
                    }
                    
                    if (empty($noteData['title']) || empty($noteData['content'])) {
                        throw new \Exception('Název a obsah poznámky jsou povinné');
                    }
                    
                    $success = $notesManager->update($noteId, $noteData);
                    $this->log("updateNote výsledek: " . ($success ? 'úspěch' : 'neúspěch'));
                    
                    if ($success) {
                        return $notesManager->getById($noteId);
                    } else {
                        throw new \Exception('Nepodařilo se aktualizovat poznámku');
                    }

                case 'deleteNote':
                    $this->log("Volám deleteNote");
                    $noteId = (int)($parameters['id'] ?? 0);
                    
                    if ($noteId <= 0) {
                        throw new \Exception('Neplatné ID poznámky');
                    }
                    
                    $success = $notesManager->delete($noteId);
                    $this->log("deleteNote výsledek: " . ($success ? 'úspěch' : 'neúspěch'));
                    
                    return ['success' => $success];

                case 'searchNotes':
                    $this->log("Volám searchNotes");
                    $query = $parameters['query'] ?? '';
                    $userId = $parameters['user_id'] ?? null;
                    
                    if (empty($query)) {
                        throw new \Exception('Hledaný výraz nesmí být prázdný');
                    }
                    
                    $result = $notesManager->search($query, $userId);
                    $this->log("searchNotes výsledek: " . count($result) . " poznámek");
                    return $result;

                case 'getCategories':
                    $this->log("Volám getCategories");
                    $userId = $parameters['user_id'] ?? null;
                    $result = $notesManager->getCategories($userId);
                    $this->log("getCategories výsledek: " . count($result) . " kategorií");
                    return $result;

                case 'getNotesByCategory':
                    $this->log("Volám getNotesByCategory");
                    $category = $parameters['category'] ?? '';
                    $userId = $parameters['user_id'] ?? null;
                    
                    if (empty($category)) {
                        throw new \Exception('Kategorie nesmí být prázdná');
                    }
                    
                    $result = $notesManager->getByCategory($category, $userId);
                    $this->log("getNotesByCategory výsledek: " . count($result) . " poznámek");
                    return $result;

                case 'getStatistics':
                    $this->log("Volám getStatistics");
                    $userId = $parameters['user_id'] ?? null;
                    $result = $notesManager->getStatistics($userId);
                    $this->log("getStatistics výsledek: " . json_encode($result));
                    return $result;

                case 'getAllData':
                    $this->log("Volám getAllData (kombinace notes + stats)");
                    $userId = $parameters['user_id'] ?? null;
                    
                    $notes = $notesManager->getAll($userId);
                    $stats = $notesManager->getStatistics($userId);
                    $categories = $notesManager->getCategories($userId);
                    
                    $result = [
                        'notes' => $notes,
                        'statistics' => $stats,
                        'categories' => $categories
                    ];
                    
                    $this->log("getAllData kompletní výsledek: " . count($notes) . " poznámek, " . count($categories) . " kategorií");
                    return $result;

                default:
                    throw new \Exception("Nepodporovaná akce: $action");
            }
            
        } catch (\Throwable $e) {
            $this->log("Chyba při zpracování AJAX akce '$action': " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Získá instanci NotesManagera
     */
    private function getNotesManager(Explorer $database): NotesManager
    {
        $this->log("Vytvářím instanci NotesManager");
        
        // Načteme službu pokud ještě není načtená
        $serviceFile = $this->modulePath . '/NotesManager.php';
        
        if (!file_exists($serviceFile)) {
            throw new \Exception("Soubor služby NotesManager nebyl nalezen: $serviceFile");
        }
        
        if (!class_exists(NotesManager::class)) {
            require_once $serviceFile;
        }
        
        if (!class_exists(NotesManager::class)) {
            throw new \Exception("Třída NotesManager nebyla nalezena");
        }
        
        $this->log("NotesManager úspěšně vytvořen");
        
        return new NotesManager($database);
    }
}