<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../../src/database.php';

if (isset($_GET['note_id'])) {
    $note_id = $_GET['note_id'];
    $user_id = $_SESSION['user_id'];

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT id, original_name as name, size FROM files WHERE note_id = :note_id AND user_id = :user_id");
    $stmt->execute(['note_id' => $note_id, 'user_id' => $user_id]);
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'files' => $files]);
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Bad Request']);
}
