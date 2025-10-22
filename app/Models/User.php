<?php

namespace App\Models;

use PDO;

class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createUser($username, $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
        );
        $stmt->execute(['username' => $username, 'password_hash' => $hash]);
        return $this->pdo->lastInsertId();
    }

    public function findByUsername($username)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyPassword($user, $password)
    {
        return password_verify($password, $user['password_hash']);
    }
}
