<?php

$config = require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "{$config['driver']}:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(100),
        avatar_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP,
        settings JSON
    );
    ";

    $pdo->exec($sql);

    echo "Table 'users' created successfully." . PHP_EOL;

} catch (PDOException $e) {
    die("Could not connect to the database {$config['database']} :" . $e->getMessage());
}
