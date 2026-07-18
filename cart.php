<?php
require_once 'db.php';
require_once 'functions.php';
session_start();

$user = isset($_SESSION['user_id']) ? getUser($pdo, $_SESSION['user_id']) : null;

if (!$user) {
    header('Location: login.php');
    exit;
}

$cartItems = getCart($pdo);
$total = 0;

// Удаление из корзины
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header('Location: cart.php');
    exit;
}

// Оформление заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    foreach ($cartItems as $item) {
        createRental($pdo, $user['id'], $item['id'], $item['cart_type'], $item['cart_period']);
        
        // Уменьшаем количество на складе
        $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
        $stmt->execute([$item['id']]);
    }
    clearCart();
    $_SESSION['toast'] = 'Заказ оформлен! Спасибо за покупку!';
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Корзина</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <?php if (isAdmin()): ?>
                    <a href="admin/index.php" class="btn btn-outline"><i class="fas fa-cog"></i> Админ</a>
                <?php endif; ?>
                <a href="cart.php" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Корзина</a>
                <a href="profile.php" class="btn btn-outline"><i class="fas fa-user"></i> <?= htmlspecialchars($user['name']) ?></a>
                <a href="logout.php" class="btn btn-outline"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Корзина</h1>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Корзина пуста</h3>
                <p><a href="index.php">Продолжить покупки</a></p>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $index => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <h4><?= htmlspecialchars($item['title']) ?></h4>
                            <p><i class="fas fa-user-edit"></i> <?= htmlspecialchars($item['author_name']) ?></p>
                            <p>
                                <span class="cart-item-type">
                                    <?= $item['cart_type'] === 'purchase' ? '🛒 Покупка' : '📖 Аренда' ?>
                                </span>
                                <?php if ($item['cart_type'] === 'rental'): ?>
                                    <span class="cart-item-period">
                                        <?php
                                        $periods = ['14_days' => '2 недели', '1_month' => '1 месяц', '3_months' => '3 месяца'];
                                        echo $periods[$item['cart_period']] ?? '';
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                            <p class="cart-item-price">
                                <?php
                                $price = $item['cart_type'] === 'purchase' ? $item['price'] : $item['price'] * 0.3;
                                $total += $price;
                                echo number_format($price, 0, ',', ' ') . ' ₽';
                                ?>
                            </p>
                        </div>
                        <a href="cart.php?remove=<?= $index ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить из корзины?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-total">
                <h3>Итого: <span><?= number_format($total, 0, ',', ' ') ?> ₽</span></h3>
                <form method="POST">
                    <button type="submit" name="checkout" class="btn btn-primary">
                        <i class="fas fa-check"></i> Оформить заказ
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>