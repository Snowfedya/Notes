<?php

// config/database_init.php

// This script should be run from the command line once during the initial setup.

$settingsFile = __DIR__ . '/settings.php';

if (!file_exists($settingsFile)) {
    echo "Database configuration file not found. Please copy 'config/settings.php.example' to 'config/settings.php' and fill in your database credentials.\n";
    exit(1);
}

$settings = require $settingsFile;
$db_config = $settings['db'];

try {
    // Connect to MySQL server without specifying a database
    $pdo = new PDO("mysql:host={$db_config['host']}", $db_config['user'], $db_config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['dbname']}`");

    // Connect to the newly created database
    $pdo->exec("USE `{$db_config['dbname']}`");

    // Get the schema from the database file
    $schema = require __DIR__ . '/../storage/database/database.php';

    // Execute the schema
    $pdo->exec($schema);

    echo "Database and tables created successfully.\n";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
