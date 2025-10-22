<?php

// config/database_init.php

$db_path = __DIR__ . '/../storage/database/focus.db';
if (!file_exists($db_path)) {
    // Create the directory if it doesn't exist
    if (!is_dir(dirname($db_path))) {
        mkdir(dirname($db_path), 0777, true);
    }
    $pdo = new PDO('sqlite:' . $db_path);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the schema from the database file
    $schema = require __DIR__ . '/../storage/database/database.php';

    // Execute the schema
    $pdo->exec($schema);
}
