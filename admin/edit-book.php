<?php
require_once '../db.php';
require_once '../functions.php';
session_start();

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$bookId = $_GET['id'] ?? 0;
$book = $bookId ? getBook($pdo, $bookId) : null;
$categories = getCategories($pdo);

$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $category_id = $_POST['category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $year = $_POST['year'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    // Проверяем, существует ли автор
    $stmt = $pdo->prepare("SELECT id FROM authors WHERE name = ?");
    $stmt->execute([$author_name]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($author) {
        $author_id = $author['id'];
    } else {
        // Создаём нового автора
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->execute([$author_name]);
        $author_id = $pdo->lastInsertId();
    }

    $data = [
        'title' => $title,
        'author_id' => $author_id,
        'category_id' => $category_id,
        'description' => $description,
        'price' => $price,
        'year' => $year,
        'stock' => $stock,
        'status' => $status
    ];

    // Загрузка картинки
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $result = uploadImage($_FILES['cover_image']);
        if (isset($result['error'])) {
            $uploadError = $result['error'];
        } else {
            $data['cover_image'] = $result['filename'];
        }
    }

    if (empty($uploadError)) {
        if ($bookId) {
            updateBook($pdo, $bookId, $data);
        } else {
            addBook($pdo, $data);
        }
        header('Location: books.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $bookId ? 'Редактировать' : 'Добавить' ?> книгу</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="../index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>Книжный</span>Магазин
                </a>
            </div>
            <div class="header-right">
                <a href="books.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Назад</a>
                <a href="../logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="form-card" style="max-width: 700px; margin-top: 30px;">
            <h2><?= $bookId ? '✏️ Редактировать книгу' : '➕ Добавить книгу' ?></h2>

            <?php if ($uploadError): ?>
                <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:18px;text-align:center;">
                    <?= $uploadError ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($book['title'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Автор</label>
                    <input type="text" name="author_name" value="<?= htmlspecialchars($book['author_name'] ?? '') ?>" placeholder="Введите имя автора" required>
                    <small style="color:#6c7a8a;display:block;margin-top:4px;">Если автора нет в базе — он будет создан автоматически</small>
                </div>

                <div class="form-group">
                    <label>Категория</label>
                    <select name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($book && $book['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" rows="5"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label>Цена (₽)</label>
                        <input type="number" name="price" step="0.01" value="<?= $book['price'] ?? '' ?>" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Год издания</label>
                        <input type="number" name="year" value="<?= $book['year'] ?? '' ?>">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Количество</label>
                        <input type="number" name="stock" value="<?= $book['stock'] ?? 1 ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label>Статус</label>
                        <select name="status">
                            <option value="available" <?= ($book && $book['status'] === 'available') ? 'selected' : '' ?>>В наличии</option>
                            <option value="rented" <?= ($book && $book['status'] === 'rented') ? 'selected' : '' ?>>Арендована</option>
                            <option value="unavailable" <?= ($book && $book['status'] === 'unavailable') ? 'selected' : '' ?>>Недоступна</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Обложка</label>
                        <?php if ($book && $book['cover_image'] && $book['cover_image'] !== 'default.jpg'): ?>
                            <div style="margin-bottom:8px;">
                                <img src="../uploads/<?= $book['cover_image'] ?>" style="max-width:100px;border-radius:8px;" alt="Обложка">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="cover_image" accept="image/*">
                        <small style="color:#6c7a8a;display:block;margin-top:4px;">Поддерживаются JPG, PNG, GIF, WEBP (макс. 5 МБ)</small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Сохранить</button>
            </form>
        </div>
    </div>
</body>
</html>