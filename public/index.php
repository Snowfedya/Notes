<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../src/database.php';

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Получаем список записных книжек
$stmt = $db->prepare("SELECT * FROM notebooks WHERE user_id = :user_id ORDER BY name");
$stmt->execute(['user_id' => $user_id]);
$notebooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем список тегов
$stmt = $db->prepare("SELECT * FROM tags WHERE user_id = :user_id ORDER BY name");
$stmt->execute(['user_id' => $user_id]);
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем заметки
$notebook_id = $_GET['notebook_id'] ?? null;
$tag_id = $_GET['tag_id'] ?? null;
$search = $_GET['search'] ?? '';

$sql = "SELECT DISTINCT n.* FROM notes n ";
$params = ['user_id' => $user_id];

if ($tag_id) {
    $sql .= " JOIN note_tags nt ON n.id = nt.note_id ";
}

$sql .= " WHERE n.user_id = :user_id ";

if ($notebook_id) {
    $sql .= " AND n.notebook_id = :notebook_id ";
    $params['notebook_id'] = $notebook_id;
}

if ($tag_id) {
    $sql .= " AND nt.tag_id = :tag_id ";
    $params['tag_id'] = $tag_id;
}

if ($search) {
    $sql .= " AND (n.title LIKE :search OR n.content LIKE :search) ";
    $params['search'] = '%' . $search . '%';
}


$sql .= " ORDER BY n.updated_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
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
        <form action="index.php" method="get">
            <input type="text" name="search" placeholder="Поиск..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Найти</button>
        </form>
        <a href="logout.php">Выйти</a>
    </div>
    <div class="container">
        <div class="sidebar">
            <h2>Записные книжки</h2>
            <ul>
                <li><a href="index.php">Все заметки</a></li>
                <?php foreach ($notebooks as $notebook): ?>
                    <li><a href="index.php?notebook_id=<?php echo $notebook['id']; ?>"><?php echo htmlspecialchars($notebook['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
            <form action="create_notebook.php" method="post">
                <input type="text" name="name" placeholder="Новая записная книжка" required>
                <button type="submit">Создать</button>
            </form>
            <hr>
            <h2>Теги</h2>
            <ul>
                <?php foreach ($tags as $tag): ?>
                    <li><a href="index.php?tag_id=<?php echo $tag['id']; ?>"><?php echo htmlspecialchars($tag['name']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="main-content">
            <a href="note.php" class="new-note-btn">Новая заметка</a>
            <ul class="notes-list">
                <?php foreach ($notes as $note): ?>
                    <li>
                        <a href="note.php?id=<?php echo $note['id']; ?>">
                            <h2><?php echo htmlspecialchars($note['title']); ?></h2>
                            <p><?php echo htmlspecialchars(substr(strip_tags($note['content']), 0, 100)); ?>...</p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
