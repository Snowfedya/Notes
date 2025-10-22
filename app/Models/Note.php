<?php

namespace App\Models;

use PDO;

class Note
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(int $userId, array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notes (user_id, folder_id, title, content, preview)
             VALUES (:user_id, :folder_id, :title, :content, :preview)'
        );
        $stmt->execute([
            ':user_id' => $userId,
            ':folder_id' => $data['folder_id'] ?? null,
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':preview' => substr($data['content'], 0, 200),
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findById(int $noteId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notes WHERE id = :id AND user_id = :user_id AND is_archived = 0');
        $stmt->execute([':id' => $noteId, ':user_id' => $userId]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);
        return $note ?: null;
    }

    public function findAllByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notes WHERE user_id = :user_id AND is_archived = 0 ORDER BY updated_at DESC');
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update(int $noteId, int $userId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notes
             SET title = :title, content = :content, preview = :preview, folder_id = :folder_id, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id AND user_id = :user_id'
        );
        return $stmt->execute([
            ':title' => $data['title'],
            ':content' => $data['content'],
            ':preview' => substr($data['content'], 0, 200),
            ':folder_id' => $data['folder_id'] ?? null,
            ':id' => $noteId,
            ':user_id' => $userId,
        ]);
    }

    public function delete(int $noteId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE notes SET is_archived = 1 WHERE id = :id AND user_id = :user_id');
        return $stmt->execute([':id' => $noteId, ':user_id' => $userId]);
    }
}
