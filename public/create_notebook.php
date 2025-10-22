<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $user_id = $_SESSION['user_id'];

    if (!empty($name)) {
        $db = get_db_connection();
        $stmt = $db->prepare("INSERT INTO notebooks (user_id, name) VALUES (:user_id, :name)");
        $stmt->execute(['user_id' => $user_id, 'name' => $name]);
    }
}

header('Location: index.php');
exit;
