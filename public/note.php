<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/database.php';

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Получаем записные книжки
$stmt = $db->prepare("SELECT * FROM notebooks WHERE user_id = :user_id ORDER BY name");
$stmt->execute(['user_id' => $user_id]);
$notebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$note_id = $_GET['id'] ?? null;
$note = null;

if ($note_id) {
    $stmt = $db->prepare("SELECT * FROM notes WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $note_id, 'user_id' => $user_id]);
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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#note-content',
        plugins: 'autosave link image lists',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
        autosave_interval: '1s',
        autosave_retention: '2m',
        autosave_restore_when_empty: true,
        init_instance_callback: function (editor) {
            editor.on('input', function (e) {
                document.getElementById('note-form').dispatchEvent(new Event('input'));
            });
        }
      });
    </script>
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
            <select name="notebook_id">
                <option value="">Без записной книжки</option>
                <?php foreach ($notebooks as $notebook): ?>
                    <option value="<?php echo $notebook['id']; ?>" <?php echo ($note && $note['notebook_id'] == $notebook['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($notebook['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <textarea id="note-content" name="content" placeholder="Начните писать..."><?php echo $note['content'] ?? ''; ?></textarea>
            <input type="text" name="tags" placeholder="Теги, через запятую" value="<?php
                if ($note_id) {
                    $stmt = $db->prepare("SELECT t.name FROM tags t JOIN note_tags nt ON t.id = nt.tag_id WHERE nt.note_id = :note_id");
                    $stmt->execute(['note_id' => $note_id]);
                    $tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    echo htmlspecialchars(implode(', ', $tags));
                }
            ?>">
        </form>
        <div id="drop-zone">
            <p>Перетащите файлы сюда для загрузки</p>
        </div>
        <div id="file-list"></div>
    </div>
    <script src="js/autosave.js"></script>
    <script src="js/upload.js"></script>
</body>
</html>
