<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/database.php';

$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM notes WHERE user_id = :user_id ORDER BY updated_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Фокус</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="navbar">
        <h1>Фокус</h1>
        <a href="logout.php">Выйти</a>
    </div>
    <div class="container">
        <a href="note.php" class="new-note-btn">Новая заметка</a>
        <ul class="notes-list">
            <?php foreach ($notes as $note): ?>
                <li>
                    <a href="note.php?id=<?php echo $note['id']; ?>">
                        <h2><?php echo htmlspecialchars($note['title']); ?></h2>
                        <p><?php echo htmlspecialchars(substr($note['content'], 0, 100)); ?>...</p>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
