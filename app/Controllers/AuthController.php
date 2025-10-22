<?php

namespace App\Controllers;

use App\Models\User;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private $userModel;
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function register(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($username) || empty($password)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => ['code' => 'BAD_REQUEST', 'message' => 'Username and password are required']
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        if ($this->userModel->findByUsername($username)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => ['code' => 'CONFLICT', 'message' => 'Username already exists']
            ]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        $userId = $this->userModel->createUser($username, $password);

        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => ['userId' => $userId, 'username' => $username]
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (empty($username) || empty($password)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => ['code' => 'BAD_REQUEST', 'message' => 'Username and password are required']
            ]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user || !$this->userModel->verifyPassword($user, $password)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Invalid credentials']
            ]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Generate session token
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime())->modify('+30 days')->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare(
            'INSERT INTO sessions (token, user_id, expires_at) VALUES (:token, :user_id, :expires_at)'
        );
        $stmt->execute(['token' => $token, 'user_id' => $user['id'], 'expires_at' => $expiresAt]);

        $response = $response->withHeader('Set-Cookie', "focus_session=$token; HttpOnly; Path=/; Max-Age=" . (30 * 24 * 60 * 60));
        $response->getBody()->write(json_encode([
            'success' => true,
            'data' => ['userId' => $user['id'], 'username' => $user['username']]
        ]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response)
    {
        $response = $response->withHeader('Set-Cookie', 'focus_session=; HttpOnly; Path=/; Max-Age=0');
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function getSession(Request $request, Response $response)
    {
        $userId = $request->getAttribute('userId');

        if ($userId) {
             $stmt = $this->pdo->prepare('SELECT id, username FROM users WHERE id = :id');
             $stmt->execute(['id' => $userId]);
             $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode([
                'success' => true,
                'data' => $user
            ]));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => ['code' => 'UNAUTHORIZED', 'message' => 'Not authenticated']
        ]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
