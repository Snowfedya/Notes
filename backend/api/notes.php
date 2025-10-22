<?php
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($note) {
                echo json_encode($note);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Note not found']);
            }
        } elseif (isset($_GET['folder_id'])) {
            $folder_id = filter_var($_GET['folder_id'], FILTER_VALIDATE_INT);
            $stmt = $pdo->prepare("SELECT * FROM notes WHERE folder_id = :folder_id ORDER BY updated_at DESC");
            $stmt->execute(['folder_id' => $folder_id]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($notes);
        } else {
            $stmt = $pdo->query("SELECT * FROM notes ORDER BY updated_at DESC");
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($notes);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $title = htmlspecialchars(strip_tags($data['title']));
        $content = htmlspecialchars($data['content']);
        $folder_id = isset($data['folder_id']) ? filter_var($data['folder_id'], FILTER_VALIDATE_INT) : null;

        $stmt = $pdo->prepare("INSERT INTO notes (title, content, folder_id) VALUES (:title, :content, :folder_id)");
        $stmt->execute(['title' => $title, 'content' => $content, 'folder_id' => $folder_id]);

        $newNoteId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id");
        $stmt->execute(['id' => $newNoteId]);
        $newNote = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(201);
        echo json_encode($newNote);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Note ID is required']);
            exit;
        }
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        $data = json_decode(file_get_contents('php://input'), true);
        $title = htmlspecialchars(strip_tags($data['title']));
        $content = htmlspecialchars($data['content']);
        $folder_id = isset($data['folder_id']) ? filter_var($data['folder_id'], FILTER_VALIDATE_INT) : null;

        $stmt = $pdo->prepare("UPDATE notes SET title = :title, content = :content, folder_id = :folder_id, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute(['title' => $title, 'content' => $content, 'folder_id' => $folder_id, 'id' => $id]);

        $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $updatedNote = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($updatedNote);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Note ID is required']);
            exit;
        }
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = :id");
        $stmt->execute(['id' => $id]);

        http_response_code(204);
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
