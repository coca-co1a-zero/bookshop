<?php
require_once 'db.php';
require_once 'functions.php';
session_start();

$user = isset($_SESSION['user_id']) ? getUser($pdo, $_SESSION['user_id']) : null;

$filters = [];
if (isset($_GET['category']) && $_GET['category'] != '') {
    $filters['category'] = $_GET['category'];
}
if (isset($_GET['author']) && $_GET['author'] != '') {
    $filters['author'] = $_GET['author'];
}
if (isset($_GET['year']) && $_GET['year'] != '') {
    $filters['year'] = $_GET['year'];
}
if (isset($_GET['search']) && $_GET['search'] != '') {
    $filters['search'] = $_GET['search'];
}

$books = getBooks($pdo, $filters);
$categories = getCategories($pdo);
$authors = getAuthors($pdo);
$years = $pdo->query("SELECT DISTINCT year FROM books ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Книжный магазин</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;500;600&display=swap');
    </style>
</head>
<body>
    <div class="container">
        <!-- ШАПКА -->
        <header class="header">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>Книжный</span>Магазин
                </a>
            </div>
            <div class="header-right">
                <form action="index.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Поиск книг..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <?php if ($user): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/index.php" class="btn btn-outline"><i class="fas fa-cog"></i> Админ</a>
                    <?php endif; ?>
                    <a href="cart.php" class="btn btn-outline"><i class="fas fa-shopping-cart"></i> Корзина</a>
                    <a href="profile.php" class="btn btn-outline"><i class="fas fa-user"></i> <?= htmlspecialchars($user['name']) ?></a>
                    <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Войти</a>
                    <a href="register.php" class="btn btn-outline"><i class="fas fa-user-plus"></i> Регистрация</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- ФИЛЬТРЫ -->
        <div class="filters">
            <form action="index.php" method="GET" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-tag"></i> Категория</label>
                    <select name="category" onchange="this.form.submit()">
                        <option value="">Все</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($_GET['category'] ?? '') == $cat['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-user-edit"></i> Автор</label>
                    <select name="author" onchange="this.form.submit()">
                        <option value="">Все</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?= htmlspecialchars($author['name']) ?>" <?= ($_GET['author'] ?? '') == $author['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($author['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> Год</label>
                    <select name="year" onchange="this.form.submit()">
                        <option value="">Все</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= ($_GET['year'] ?? '') == $year ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (isset($_GET['category']) || isset($_GET['author']) || isset($_GET['year']) || isset($_GET['search'])): ?>
                    <a href="index.php" class="btn btn-sm btn-outline"><i class="fas fa-times"></i> Сбросить</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- КАТАЛОГ КНИГ -->
        <div class="books-grid">
            <?php if (empty($books)): ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>Книги не найдены</h3>
                    <p>Попробуйте изменить параметры поиска</p>
                </div>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <?php 
                    $cover = (!empty($book['cover_image']) && $book['cover_image'] !== 'default.jpg') 
                        ? 'uploads/' . $book['cover_image'] 
                        : 'images/default.jpg'; 
                    ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <img src="<?= $cover ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                            <?php if ($book['status'] !== 'available'): ?>
                                <div class="book-status <?= $book['status'] ?>">
                                    <?= $book['status'] === 'rented' ? 'Арендована' : 'Недоступна' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3><a href="book.php?id=<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?></a></h3>
                            <p><i class="fas fa-user-edit"></i> <?= htmlspecialchars($book['author_name']) ?></p>
                            <p><i class="fas fa-tag"></i> <?= htmlspecialchars($book['category_name']) ?></p>
                            <p class="book-price"><i class="fas fa-ruble-sign"></i> <?= number_format($book['price'], 0, ',', ' ') ?> ₽</p>
                            <?php if ($book['stock'] > 0 && $book['status'] === 'available'): ?>
                                <span class="book-stock">В наличии: <?= $book['stock'] ?> шт.</span>
                            <?php else: ?>
                                <span class="book-stock out-of-stock">Нет в наличии</span>
                            <?php endif; ?>
                            <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-primary btn-sm book-detail-btn">
                                <i class="fas fa-eye"></i> Подробнее
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>