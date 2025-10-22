<?php

// config/database.php

$settingsFile = __DIR__ . '/settings.php';

if (!file_exists($settingsFile)) {
    // If the settings file does not exist, you can return a null connection
    // or handle the error as appropriate for your application.
    // For now, we'll throw an exception.
    throw new \Exception("Database configuration file not found. Please copy 'config/settings.php.example' to 'config/settings.php' and fill in your database credentials.");
}

$settings = require $settingsFile;
$db_config = $settings['db'];

$dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass'], $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

return $pdo;
