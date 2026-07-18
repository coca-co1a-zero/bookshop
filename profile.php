<?php
require_once 'db.php';
require_once 'functions.php';
session_start();

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUser($pdo, $_SESSION['user_id']);
$rentals = getUserRentals($pdo, $_SESSION['user_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>Книжный</span>Магазин
                </a>
            </div>
            <div class="header-right">
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline"><i class="fas fa-cog"></i> Админ</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-outline"><i class="fas fa-shopping-cart"></i> Корзина</a>
                <a href="profile.php" class="btn btn-primary"><i class="fas fa-user"></i> <?= htmlspecialchars($user['name']) ?></a>
                <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="profile">
            <h1><i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['name']) ?></h1>
            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>

            <h2><i class="fas fa-book"></i> Мои аренды</h2>
            <?php if (empty($rentals)): ?>
                <div class="empty-state" style="padding: 30px;">
                    <i class="fas fa-book"></i>
                    <p>У вас пока нет арендованных книг</p>
                    <a href="index.php" class="btn btn-primary">Начать покупки</a>
                </div>
            <?php else: ?>
                <div class="rentals-list">
                    <?php foreach ($rentals as $rental): ?>
                        <div class="rental-item <?= $rental['current_status'] === 'overdue' ? 'overdue' : '' ?>">
                            <div class="rental-info">
                                <h4><?= htmlspecialchars($rental['title']) ?></h4>
                                <p>
                                    <span class="rental-type">
                                        <?= $rental['type'] === 'purchase' ? '🛒 Покупка' : '📖 Аренда' ?>
                                    </span>
                                    <span class="rental-period">
                                        <?php if ($rental['type'] === 'rental'): ?>
                                            <?php
                                            $periods = ['14_days' => '2 недели', '1_month' => '1 месяц', '3_months' => '3 месяца'];
                                            echo $periods[$rental['rental_period']] ?? '';
                                            ?>
                                        <?php endif; ?>
                                    </span>
                                </p>
                                <p>
                                    <i class="fas fa-calendar-alt"></i> С <?= date('d.m.Y', strtotime($rental['start_date'])) ?> 
                                    по <?= date('d.m.Y', strtotime($rental['end_date'])) ?>
                                </p>
                                <p><strong><?= number_format($rental['total_price'], 0, ',', ' ') ?> ₽</strong></p>
                            </div>
                            <div>
                                <span class="status-badge <?= $rental['current_status'] === 'active' ? 'status-public' : ($rental['current_status'] === 'overdue' ? 'status-private' : 'status-request') ?>">
                                    <?= $rental['current_status'] === 'active' ? 'Активна' : ($rental['current_status'] === 'overdue' ? '❌ Просрочена' : 'Возвращена') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>