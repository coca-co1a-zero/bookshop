<?php
require_once 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['password_confirm'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Заполните все поля';
    } elseif ($password !== $confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть минимум 6 символов';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hash]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Пользователь с таким email уже существует';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-card" style="margin-top: 50px;">
            <a href="index.php" class="back-link" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;color:#6c7a8a;text-decoration:none;">
                <i class="fas fa-arrow-left"></i> На главную
            </a>
            <h2><i class="fas fa-user-plus"></i> Регистрация</h2>
            
            <?php if ($error): ?>
                <div style="background:#f8d7da;color:#721c24;padding:12px;border-radius:8px;margin-bottom:18px;text-align:center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Имя</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Введите ваше имя" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="example@mail.com" required>
                </div>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="Минимум 6 символов" required>
                </div>
                <div class="form-group">
                    <label>Подтверждение пароля</label>
                    <input type="password" name="password_confirm" placeholder="Повторите пароль" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Зарегистрироваться</button>
            </form>
            <div class="form-footer">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </div>
        </div>
    </div>
</body>
</html>