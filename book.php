<?php
require_once 'db.php';
require_once 'functions.php';
session_start();

$bookId = $_GET['id'] ?? 0;
$book = getBook($pdo, $bookId);

if (!$book) {
    die('Книга не найдена');
}

$user = isset($_SESSION['user_id']) ? getUser($pdo, $_SESSION['user_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $type = $_POST['type'];
    $period = $_POST['period'] ?? '14_days';
    
    if ($type === 'purchase' && $book['stock'] > 0) {
        addToCart($bookId, 'purchase');
        $_SESSION['toast'] = 'Книга добавлена в корзину для покупки';
    } elseif ($type === 'rental' && $book['stock'] > 0) {
        addToCart($bookId, 'rental', $period);
        $_SESSION['toast'] = 'Книга добавлена в корзину для аренды';
    } else {
        $_SESSION['toast_error'] = 'Книга временно недоступна';
    }
    header("Location: book.php?id=$bookId");
    exit;
}

$toastMessage = $_SESSION['toast'] ?? null;
$toastError = $_SESSION['toast_error'] ?? null;
unset($_SESSION['toast'], $_SESSION['toast_error']);

$cover = (!empty($book['cover_image']) && $book['cover_image'] !== 'default.jpg') 
    ? 'uploads/' . $book['cover_image'] 
    : 'images/default.jpg';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if ($toastMessage): ?>
            <div class="toast show"><?= htmlspecialchars($toastMessage) ?></div>
        <?php endif; ?>
        <?php if ($toastError): ?>
            <div class="toast show error"><?= htmlspecialchars($toastError) ?></div>
        <?php endif; ?>

        <header class="header">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>Книжный</span>Магазин
                </a>
            </div>
            <div class="header-right">
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

        <div class="book-detail">
            <div class="book-detail-cover">
                <img src="<?= $cover ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                <?php if ($book['status'] !== 'available'): ?>
                    <div class="book-status <?= $book['status'] ?>">
                        <?= $book['status'] === 'rented' ? 'Арендована' : 'Недоступна' ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="book-detail-info">
                <h1><?= htmlspecialchars($book['title']) ?></h1>
                <div class="book-detail-meta">
                    <span><i class="fas fa-user-edit"></i> <?= htmlspecialchars($book['author_name']) ?></span>
                    <span><i class="fas fa-tag"></i> <?= htmlspecialchars($book['category_name']) ?></span>
                    <span><i class="fas fa-calendar-alt"></i> <?= $book['year'] ?></span>
                </div>
                <div class="book-detail-description">
                    <?= nl2br(htmlspecialchars($book['description'])) ?>
                </div>
                <div class="book-detail-price">
                    <span class="price"><?= number_format($book['price'], 0, ',', ' ') ?> ₽</span>
                    <?php if ($book['stock'] > 0 && $book['status'] === 'available'): ?>
                        <span class="stock">В наличии: <?= $book['stock'] ?> шт.</span>
                    <?php else: ?>
                        <span class="stock out-of-stock">Нет в наличии</span>
                    <?php endif; ?>
                </div>

                <?php if ($user && $book['stock'] > 0 && $book['status'] === 'available'): ?>
                    <form method="POST" class="book-actions">
                        <div class="book-action-group">
                            <label>
                                <input type="radio" name="type" value="purchase" checked onchange="toggleRentalPeriod(this)">
                                <i class="fas fa-shopping-bag"></i> Купить
                            </label>
                            <label>
                                <input type="radio" name="type" value="rental" onchange="toggleRentalPeriod(this)">
                                <i class="fas fa-clock"></i> Арендовать
                            </label>
                        </div>
                        <div class="book-action-period" id="rental-period" style="display:none;">
                            <label><i class="fas fa-calendar-alt"></i> Срок аренды:</label>
                            <select name="period">
                                <option value="14_days">2 недели</option>
                                <option value="1_month">1 месяц</option>
                                <option value="3_months">3 месяца</option>
                            </select>
                        </div>
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> Добавить в корзину
                        </button>
                    </form>
                    <script>
                        function toggleRentalPeriod(el) {
                            document.getElementById('rental-period').style.display = el.value === 'rental' ? 'block' : 'none';
                        }
                    </script>
                <?php elseif (!$user): ?>
                    <div class="book-action-login">
                        <p><a href="login.php">Войдите</a>, чтобы купить или арендовать книгу</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>