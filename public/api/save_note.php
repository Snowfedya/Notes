<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../../src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_id = $_POST['note_id'] ?? null;
    $title = $_POST['title'];
    $content = $_POST['content'];
    $notebook_id = $_POST['notebook_id'] ?: null;
    $user_id = $_SESSION['user_id'];

    $db = get_db_connection();

    if ($note_id) {
        $stmt = $db->prepare("UPDATE notes SET title = :title, content = :content, notebook_id = :notebook_id, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['title' => $title, 'content' => $content, 'notebook_id' => $notebook_id, 'id' => $note_id, 'user_id' => $user_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO notes (user_id, title, content, notebook_id) VALUES (:user_id, :title, :content, :notebook_id)");
        $stmt->execute(['user_id' => $user_id, 'title' => $title, 'content' => $content, 'notebook_id' => $notebook_id]);
        $note_id = $db->lastInsertId();
    }

    // Обработка тегов
    $tags_str = $_POST['tags'] ?? '';
    $tags = array_map('trim', explode(',', $tags_str));

    // Удаляем старые теги
    $stmt = $db->prepare("DELETE FROM note_tags WHERE note_id = :note_id");
    $stmt->execute(['note_id' => $note_id]);

    foreach ($tags as $tag_name) {
        if (empty($tag_name)) continue;

        // Ищем тег или создаем новый
        $stmt = $db->prepare("SELECT id FROM tags WHERE user_id = :user_id AND name = :name");
        $stmt->execute(['user_id' => $user_id, 'name' => $tag_name]);
        $tag_id = $stmt->fetchColumn();

        if (!$tag_id) {
            $stmt = $db->prepare("INSERT INTO tags (user_id, name) VALUES (:user_id, :name)");
            $stmt->execute(['user_id' => $user_id, 'name' => $tag_name]);
            $tag_id = $db->lastInsertId();
        }

        // Связываем тег с заметкой
        $stmt = $db->prepare("INSERT OR IGNORE INTO note_tags (note_id, tag_id) VALUES (:note_id, :tag_id)");
        $stmt->execute(['note_id' => $note_id, 'tag_id' => $tag_id]);
    }

    echo json_encode(['status' => 'success', 'note_id' => $note_id]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
