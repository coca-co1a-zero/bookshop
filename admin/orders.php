<?php
require_once '../db.php';
require_once '../functions.php';
session_start();

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$rentals = getAllRentals($pdo);

if (isset($_POST['send_reminder']) && isset($_POST['rental_id'])) {
    $rentalId = $_POST['rental_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM rentals WHERE id = ?");
    $stmt->execute([$rentalId]);
    $rental = $stmt->fetch();
    if ($rental) {
        sendOverdueNotification($pdo, $rentalId, $rental['user_id']);
        $_SESSION['toast'] = 'Уведомление отправлено!';
        header('Location: orders.php');
        exit;
    }
}

$toastMessage = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Управление арендами</title>
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
                <span style="color: #6c7a8a; margin-left: 16px; font-size: 14px;">⚡ Управление арендами</span>
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
                    <li><a href="books.php"><i class="fas fa-book"></i> Книги</a></li>
                    <li><a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Аренды</a></li>
                </ul>
            </div>
            <div class="admin-content">
                <div class="admin-header">
                    <h1>Управление арендами</h1>
                </div>

                <table class="admin-table">
                    <tr>
                        <th>Книга</th>
                        <th>Пользователь</th>
                        <th>Email</th>
                        <th>Тип</th>
                        <th>Начало</th>
                        <th>Конец</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                    <?php foreach ($rentals as $rental): ?>
                        <tr>
                            <td><?= htmlspecialchars($rental['title']) ?></td>
                            <td><?= htmlspecialchars($rental['user_name']) ?></td>
                            <td><?= htmlspecialchars($rental['email']) ?></td>
                            <td><?= $rental['type'] === 'purchase' ? '🛒 Покупка' : '📖 Аренда' ?></td>
                            <td><?= date('d.m.Y', strtotime($rental['start_date'])) ?></td>
                            <td><?= date('d.m.Y', strtotime($rental['end_date'])) ?></td>
                            <td>
                                <span class="status-badge <?= $rental['current_status'] === 'active' ? 'status-public' : ($rental['current_status'] === 'overdue' ? 'status-private' : 'status-request') ?>">
                                    <?= $rental['current_status'] === 'active' ? 'Активна' : ($rental['current_status'] === 'overdue' ? 'Просрочена' : 'Возвращена') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($rental['current_status'] === 'overdue'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="rental_id" value="<?= $rental['id'] ?>">
                                        <button type="submit" name="send_reminder" class="btn btn-sm btn-warning">
                                            <i class="fas fa-bell"></i> Напомнить
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>