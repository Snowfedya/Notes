<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\Database;

class AuthController
{
    public function register()
    {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists']);
            return;
        }

        $user = new User($email, $password, $name);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)");
        $stmt->execute([
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->password_hash,
        ]);

        echo json_encode(['message' => 'User registered successfully']);
    }

    public function login()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['message' => 'Login successful']);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();

        echo json_encode(['message' => 'Logout successful']);
    }
}
