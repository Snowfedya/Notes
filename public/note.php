<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../database/database.php';

$db = get_db_connection();
$note_id = $_GET['id'] ?? null;
$note = null;

if ($note_id) {
    $stmt = $db->prepare("SELECT * FROM notes WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $note_id, 'user_id' => $_SESSION['user_id']]);
    $note = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $note ? htmlspecialchars($note['title']) : 'Новая заметка'; ?> — Фокус</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/note.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Назад к заметкам</a>
        <a href="logout.php">Выйти</a>
    </div>
    <div class="container">
        <form id="note-form">
            <?php if ($note_id): ?>
                <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
            <?php endif; ?>
            <input type="text" name="title" placeholder="Заголовок" value="<?php echo htmlspecialchars($note['title'] ?? ''); ?>" required>
            <textarea name="content" placeholder="Начните писать..."><?php echo htmlspecialchars($note['content'] ?? ''); ?></textarea>
        </form>
    </div>
    <script src="js/autosave.js"></script>
</body>
</html>
