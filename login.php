<?php
require_once 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                header('Location: index.php');
                exit;
            } else {
                $error = 'Неверный пароль';
            }
        } else {
            $error = 'Пользователь с таким email не найден';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="container">
    <div class="form-card" style="margin-top:50px;">
        <a href="index.php" style="display:inline-flex;align-items:center;gap:6px;color:#6c7a8a;text-decoration:none;margin-bottom:16px;">
            <i class="fas fa-arrow-left"></i> На главную
        </a>
        <h2><i class="fas fa-sign-in-alt"></i> Вход</h2>

        <?php if ($error): ?>
            <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:18px;text-align:center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
                <i class="fas fa-sign-in-alt"></i> Войти
            </button>
        </form>

        <div class="form-footer">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</div>
</body>
</html>