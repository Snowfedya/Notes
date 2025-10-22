<?php

namespace App\Controllers;

use App\Models\Note;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NoteController
{
    private $noteModel;

    public function __construct(PDO $pdo)
    {
        $this->noteModel = new Note($pdo);
    }

    public function createNote(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $response->withStatus(401);
        }

        $data = $request->getParsedBody();
        $noteId = $this->noteModel->create($userId, $data);

        $response->getBody()->write(json_encode(['success' => true, 'data' => ['id' => $noteId]]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function getNote(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $response->withStatus(401);
        }

        $note = $this->noteModel->findById((int)$args['id'], $userId);

        if (!$note) {
            return $response->withStatus(404);
        }

        $response->getBody()->write(json_encode(['success' => true, 'data' => $note]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getAllNotes(Request $request, Response $response): Response
    {
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $response->withStatus(401);
        }

        $notes = $this->noteModel->findAllByUser($userId);

        $response->getBody()->write(json_encode(['success' => true, 'data' => $notes]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateNote(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $response->withStatus(401);
        }

        $data = $request->getParsedBody();
        $this->noteModel->update((int)$args['id'], $userId, $data);

        return $response->withStatus(204);
    }

    public function deleteNote(Request $request, Response $response, array $args): Response
    {
        $userId = $request->getAttribute('userId');
        if (!$userId) {
            return $response->withStatus(401);
        }

        $this->noteModel->delete((int)$args['id'], $userId);

        return $response->withStatus(204);
    }
}
