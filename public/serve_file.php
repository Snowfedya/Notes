<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

require_once '../src/database.php';

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    $db = get_db_connection();
    $stmt = $db->prepare("SELECT * FROM files WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $file_id, 'user_id' => $user_id]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $file_path = '../files/' . $file['stored_name'];
        if (file_exists($file_path)) {
            header('Content-Type: ' . $file['mime_type']);
            header('Content-Disposition: inline; filename="' . $file['original_name'] . '"');
            header('Content-Length: ' . $file['size']);
            readfile($file_path);
            exit;
        }
    }
}

http_response_code(404);
echo "File not found";
