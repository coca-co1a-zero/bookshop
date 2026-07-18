<?php
require_once '../db.php';
require_once '../functions.php';
session_start();

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$user = getUser($pdo, $_SESSION['user_id']);

$stats = [
    'books' => $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn(),
    'rentals' => $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'active'")->fetchColumn(),
    'overdue' => $pdo->query("SELECT COUNT(*) FROM rentals WHERE status = 'active' AND end_date < CURDATE()")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
];

// Отправка напоминания
if (isset($_POST['send_reminder']) && isset($_POST['rental_id'])) {
    $rentalId = $_POST['rental_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM rentals WHERE id = ?");
    $stmt->execute([$rentalId]);
    $rental = $stmt->fetch();
    if ($rental) {
        sendOverdueNotification($pdo, $rentalId, $rental['user_id']);
        $_SESSION['toast'] = 'Уведомление отправлено!';
        header('Location: index.php');
        exit;
    }
}

$toastMessage = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

$overdue = checkOverdueRentals($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Админ-панель</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if ($toastMessage): ?>
            <div class="toast show"><?= htmlspecialchars($toastMessage) ?></div>
        <?php endif; ?>

        <header class="header">
            <div class="header-left">
                <a href="../index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>Книжный</span>Магазин
                </a>
                <span style="color: #6c7a8a; margin-left: 16px; font-size: 14px;">⚡ Админ-панель</span>
            </div>
            <div class="header-right">
                <a href="../index.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> На сайт</a>
                <a href="../logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="admin-dashboard">
            <div class="admin-sidebar">
                <ul>
                    <li><a href="index.php" class="active"><i class="fas fa-dashboard"></i> Главная</a></li>
                    <li><a href="books.php"><i class="fas fa-book"></i> Книги</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> Аренды</a></li>
                </ul>
            </div>
            <div class="admin-content">
                <h1>Добро пожаловать, <?= htmlspecialchars($user['name']) ?>!</h1>
                
                <div class="admin-stats">
                    <div class="stat-card">
                        <i class="fas fa-book"></i>
                        <span class="stat-number"><?= $stats['books'] ?></span>
                        <span class="stat-label">Книг в каталоге</span>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="stat-number"><?= $stats['rentals'] ?></span>
                        <span class="stat-label">Активных аренд</span>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="stat-number"><?= $stats['overdue'] ?></span>
                        <span class="stat-label">Просрочено</span>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <span class="stat-number"><?= $stats['users'] ?></span>
                        <span class="stat-label">Пользователей</span>
                    </div>
                </div>

                <div class="admin-section">
                    <h2><i class="fas fa-clock"></i> Просроченные аренды</h2>
                    <?php if (empty($overdue)): ?>
                        <div class="admin-empty">✅ Все аренды в сроке</div>
                    <?php else: ?>
                        <table class="admin-table">
                            <tr>
                                <th>Книга</th>
                                <th>Пользователь</th>
                                <th>Должна быть возвращена</th>
                                <th>Действие</th>
                            </tr>
                            <?php foreach ($overdue as $rental): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rental['title']) ?></td>
                                    <td><?= htmlspecialchars($rental['name']) ?></td>
                                    <td><?= date('d.m.Y', strtotime($rental['end_date'])) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">
                                            <button type="submit" name="send_reminder" class="btn btn-sm btn-warning">
                                                <i class="fas fa-bell"></i> Напомнить
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
