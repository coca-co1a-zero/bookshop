<?php
require_once '../db.php';
require_once '../functions.php';
session_start();

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

if (isset($_GET['delete'])) {
    deleteBook($pdo, $_GET['delete']);
    header('Location: books.php');
    exit;
}

$books = getBooks($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Управление книгами</title>
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
                <span style="color: #6c7a8a; margin-left: 16px; font-size: 14px;">⚡ Управление книгами</span>
            </div>
            <div class="header-right">
                <a href="index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Назад</a>
                <a href="../logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="admin-dashboard">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="index.php"><i class="fas fa-dashboard"></i> Главная</a></li>
                    <li><a href="books.php" class="active"><i class="fas fa-book"></i> Книги</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Аренды</a></li>
                </ul>
            </div>
            <div class="admin-content">
                <div class="admin-header">
                    <h1>Управление книгами</h1>
                    <a href="edit-book.php" class="btn btn-primary"><i class="fas fa-plus"></i> Добавить книгу</a>
                </div>

                <table class="admin-table">
                    <tr>
                        <th>Обложка</th>
                        <th>Название</th>
                        <th>Автор</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td>
                                <?php 
                                $cover = (!empty($book['cover_image']) && $book['cover_image'] !== 'default.jpg') 
                                    ? '../uploads/' . $book['cover_image'] 
                                    : '../images/default.jpg'; 
                                ?>
                                <img src="<?= $cover ?>" style="width:50px;height:70px;object-fit:cover;border-radius:4px;" alt="">
                            </td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author_name']) ?></td>
                            <td><?= htmlspecialchars($book['category_name']) ?></td>
                            <td><?= number_format($book['price'], 0, ',', ' ') ?> ₽</td>
                            <td>
                                <span class="status-badge <?= $book['status'] === 'available' ? 'status-public' : 'status-private' ?>">
                                    <?= $book['status'] === 'available' ? 'Доступна' : ($book['status'] === 'rented' ? 'Арендована' : 'Недоступна') ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit-book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                <a href="books.php?delete=<?= $book['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить книгу?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>