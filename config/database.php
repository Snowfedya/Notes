<?php

// config/database.php

$db_path = __DIR__ . '/../storage/database/focus.db';
$pdo = new PDO('sqlite:' . $db_path);

// Set error mode to exception
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

return $pdo;
