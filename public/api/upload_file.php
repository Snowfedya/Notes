<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../../src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $note_id = $_POST['note_id'];
    $user_id = $_SESSION['user_id'];

    $file = $_FILES['file'];
    $upload_dir = '../../files/';

    // Проверяем, что заметка принадлежит пользователю
    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id FROM notes WHERE id = :note_id AND user_id = :user_id");
    $stmt->execute(['note_id' => $note_id, 'user_id' => $user_id]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Forbidden']);
        exit;
    }

    $original_name = $file['name'];
    $mime_type = $file['type'];
    $size = $file['size'];
    $stored_name = uniqid('', true) . '.' . pathinfo($original_name, PATHINFO_EXTENSION);

    if (move_uploaded_file($file['tmp_name'], $upload_dir . $stored_name)) {
        $stmt = $db->prepare("INSERT INTO files (note_id, user_id, original_name, stored_name, mime_type, size) VALUES (:note_id, :user_id, :original_name, :stored_name, :mime_type, :size)");
        $stmt->execute([
            'note_id' => $note_id,
            'user_id' => $user_id,
            'original_name' => $original_name,
            'stored_name' => $stored_name,
            'mime_type' => $mime_type,
            'size' => $size
        ]);
        $file_id = $db->lastInsertId();

        echo json_encode(['status' => 'success', 'file' => ['id' => $file_id, 'name' => $original_name, 'size' => $size]]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bad Request']);
}
