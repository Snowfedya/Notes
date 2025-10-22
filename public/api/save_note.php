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
    $user_id = $_SESSION['user_id'];

    $db = get_db_connection();

    if ($note_id) {
        $stmt = $db->prepare("UPDATE notes SET title = :title, content = :content, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id");
        $stmt->execute(['title' => $title, 'content' => $content, 'id' => $note_id, 'user_id' => $user_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO notes (user_id, title, content) VALUES (:user_id, :title, :content)");
        $stmt->execute(['user_id' => $user_id, 'title' => $title, 'content' => $content]);
        $note_id = $db->lastInsertId();
    }

    echo json_encode(['status' => 'success', 'note_id' => $note_id]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
}
