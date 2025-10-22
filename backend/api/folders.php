<?php
require_once __DIR__ . '/../database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $pdo->query("SELECT * FROM folders ORDER BY name");
        $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($folders);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $name = htmlspecialchars(strip_tags($data['name']));
        $parentId = isset($data['parent_id']) ? filter_var($data['parent_id'], FILTER_VALIDATE_INT) : null;

        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (:name, :parent_id)");
        $stmt->execute(['name' => $name, 'parent_id' => $parentId]);

        $newFolderId = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = :id");
        $stmt->execute(['id' => $newFolderId]);
        $newFolder = $stmt->fetch(PDO::FETCH_ASSOC);

        http_response_code(201);
        echo json_encode($newFolder);
        break;

    case 'PUT':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Folder ID is required']);
            exit;
        }
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        $data = json_decode(file_get_contents('php://input'), true);
        $name = htmlspecialchars(strip_tags($data['name']));

        $stmt = $pdo->prepare("UPDATE folders SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $name, 'id' => $id]);

        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $updatedFolder = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($updatedFolder);
        break;

    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Folder ID is required']);
            exit;
        }
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

        // Set folder_id to NULL for all notes in this folder
        $stmt = $pdo->prepare("UPDATE notes SET folder_id = NULL WHERE folder_id = :id");
        $stmt->execute(['id' => $id]);

        // Delete the folder
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = :id");
        $stmt->execute(['id' => $id]);

        http_response_code(204);
        break;

    default:
        http_response_code(405);
        echo json_encode(['message' => 'Method Not Allowed']);
        break;
}
