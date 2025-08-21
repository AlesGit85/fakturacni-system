<?php

declare(strict_types=1);

namespace Modules\Tenant1\Notes;

use Nette;
use DateTime;

/**
 * Správce poznámek - práce s databází
 */
class NotesManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Explorer */
    private $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    /**
     * Získá všechny poznámky
     */
    public function getAll(?int $userId = null): array
    {
        $query = $this->database->table('notes')
            ->where('is_archived', 0)
            ->order('created_at DESC');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notes = [];
        foreach ($query as $note) {
            $notes[] = $this->formatNote($note);
        }

        return $notes;
    }

    /**
     * Získá poznámku podle ID
     */
    public function getById(int $id): ?array
    {
        $note = $this->database->table('notes')->get($id);
        
        if (!$note || $note->is_archived) {
            return null;
        }

        return $this->formatNote($note);
    }

    /**
     * Přidá novou poznámku
     */
    public function add(array $data): int
    {
        // Ošetření dat
        $noteData = [
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'category' => !empty($data['category']) ? trim($data['category']) : null,
            'priority' => $data['priority'] ?? 'normal',
            'tags' => !empty($data['tags']) ? trim($data['tags']) : null,
            'user_id' => $data['user_id'] ?? null,
            'created_at' => new DateTime(),
            'is_archived' => 0
        ];

        $result = $this->database->table('notes')->insert($noteData);
        return $result->id;
    }

    /**
     * Aktualizuje poznámku
     */
    public function update(int $id, array $data): bool
    {
        $updateData = [
            'title' => trim($data['title']),
            'content' => trim($data['content']),
            'category' => !empty($data['category']) ? trim($data['category']) : null,
            'priority' => $data['priority'] ?? 'normal',
            'tags' => !empty($data['tags']) ? trim($data['tags']) : null,
            'updated_at' => new DateTime()
        ];

        $result = $this->database->table('notes')
            ->where('id', $id)
            ->where('is_archived', 0)
            ->update($updateData);

        return $result > 0;
    }

    /**
     * Smaže poznámku (archivuje)
     */
    public function delete(int $id): bool
    {
        $result = $this->database->table('notes')
            ->where('id', $id)
            ->update([
                'is_archived' => 1,
                'updated_at' => new DateTime()
            ]);

        return $result > 0;
    }

    /**
     * Hledá v poznámkách
     */
    public function search(string $query, ?int $userId = null): array
    {
        $searchQuery = $this->database->table('notes')
            ->where('is_archived', 0)
            ->where('title LIKE ? OR content LIKE ? OR tags LIKE ?', 
                "%$query%", "%$query%", "%$query%")
            ->order('created_at DESC');

        if ($userId) {
            $searchQuery->where('user_id', $userId);
        }

        $notes = [];
        foreach ($searchQuery as $note) {
            $notes[] = $this->formatNote($note);
        }

        return $notes;
    }

    /**
     * Získá poznámky podle kategorie
     */
    public function getByCategory(string $category, ?int $userId = null): array
    {
        $query = $this->database->table('notes')
            ->where('is_archived', 0)
            ->where('category', $category)
            ->order('created_at DESC');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $notes = [];
        foreach ($query as $note) {
            $notes[] = $this->formatNote($note);
        }

        return $notes;
    }

    /**
     * Získá seznam všech kategorií
     */
    public function getCategories(?int $userId = null): array
    {
        $query = $this->database->table('notes')
            ->select('DISTINCT category')
            ->where('is_archived', 0)
            ->where('category IS NOT NULL')
            ->where('category != ""');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $categories = [];
        foreach ($query as $row) {
            $categories[] = $row->category;
        }

        sort($categories);
        return $categories;
    }

    /**
     * Získá statistiky poznámek
     */
    public function getStatistics(?int $userId = null): array
    {
        $baseQuery = $this->database->table('notes')
            ->where('is_archived', 0);

        if ($userId) {
            $baseQuery->where('user_id', $userId);
        }

        $total = $baseQuery->count();

        // Poznámky tento týden
        $weekAgo = new DateTime('-1 week');
        $thisWeek = $this->database->table('notes')
            ->where('is_archived', 0)
            ->where('created_at >= ?', $weekAgo);

        if ($userId) {
            $thisWeek->where('user_id', $userId);
        }

        $thisWeekCount = $thisWeek->count();

        // Poznámky podle priority
        $priorities = [];
        foreach (['low', 'normal', 'high'] as $priority) {
            $priorityQuery = $this->database->table('notes')
                ->where('is_archived', 0)
                ->where('priority', $priority);

            if ($userId) {
                $priorityQuery->where('user_id', $userId);
            }

            $priorities[$priority] = $priorityQuery->count();
        }

        return [
            'total' => $total,
            'thisWeek' => $thisWeekCount,
            'priorities' => $priorities,
            'categories' => count($this->getCategories($userId))
        ];
    }

    /**
     * Formátuje poznámku pro frontend
     */
    private function formatNote($note): array
    {
        return [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'category' => $note->category,
            'priority' => $note->priority,
            'tags' => $note->tags ? explode(',', $note->tags) : [],
            'created_at' => $note->created_at,
            'updated_at' => $note->updated_at,
            'user_id' => $note->user_id,
            'formatted_date' => $note->created_at->format('d.m.Y H:i'),
            'formatted_updated' => $note->updated_at ? $note->updated_at->format('d.m.Y H:i') : null
        ];
    }
}