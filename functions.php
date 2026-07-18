<?php
session_start();

// ============================================
// АУТЕНТИФИКАЦИЯ
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUser($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ============================================
// КНИГИ
// ============================================

function getBooks($pdo, $filters = []) {
    $sql = "SELECT b.*, a.name as author_name, c.name as category_name 
            FROM books b
            JOIN authors a ON b.author_id = a.id
            JOIN categories c ON b.category_id = c.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filters['category'])) {
        $sql .= " AND c.name = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['author'])) {
        $sql .= " AND a.name = ?";
        $params[] = $filters['author'];
    }
    
    if (!empty($filters['year'])) {
        $sql .= " AND b.year = ?";
        $params[] = $filters['year'];
    }
    
    if (!empty($filters['search'])) {
        $sql .= " AND (b.title LIKE ? OR a.name LIKE ?)";
        $params[] = '%' . $filters['search'] . '%';
        $params[] = '%' . $filters['search'] . '%';
    }
    
    $sql .= " ORDER BY b.title ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBook($pdo, $id) {
    $stmt = $pdo->prepare("SELECT b.*, a.name as author_name, c.name as category_name 
                           FROM books b
                           JOIN authors a ON b.author_id = a.id
                           JOIN categories c ON b.category_id = c.id
                           WHERE b.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addBook($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO books (title, author_id, category_id, description, price, year, stock, status, cover_image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $data['title'],
        $data['author_id'],
        $data['category_id'],
        $data['description'],
        $data['price'],
        $data['year'],
        $data['stock'],
        $data['status'],
        $data['cover_image'] ?? 'default.jpg'
    ]);
}

function updateBook($pdo, $id, $data) {
    $sql = "UPDATE books SET 
            title = ?, author_id = ?, category_id = ?, 
            description = ?, price = ?, year = ?, stock = ?, status = ?";
    $params = [
        $data['title'],
        $data['author_id'],
        $data['category_id'],
        $data['description'],
        $data['price'],
        $data['year'],
        $data['stock'],
        $data['status']
    ];
    
    if (isset($data['cover_image']) && $data['cover_image']) {
        $sql .= ", cover_image = ?";
        $params[] = $data['cover_image'];
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $id;
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function deleteBook($pdo, $id) {
    // Удаляем картинку, если она есть
    $book = getBook($pdo, $id);
    if ($book && $book['cover_image'] && $book['cover_image'] !== 'default.jpg') {
        $file = __DIR__ . '/uploads/' . $book['cover_image'];
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    return $stmt->execute([$id]);
}

// ============================================
// ЗАГРУЗКА КАРТИНОК
// ============================================

function uploadImage($file) {
    $target_dir = __DIR__ . '/uploads/';
    
    // Создаём папку, если её нет
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Генерируем уникальное имя
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target_file = $target_dir . $filename;
    
    // Проверяем тип файла
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
        return ['error' => 'Разрешены только JPG, PNG, GIF, WEBP'];
    }
    
    // Проверяем размер (максимум 5 МБ)
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Файл слишком большой (максимум 5 МБ)'];
    }
    
    // Перемещаем файл
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['error' => 'Ошибка загрузки файла'];
}

// ============================================
// КАТЕГОРИИ И АВТОРЫ
// ============================================

function getCategories($pdo) {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAuthors($pdo) {
    $stmt = $pdo->query("SELECT * FROM authors ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ============================================
// КОРЗИНА И АРЕНДА
// ============================================

function addToCart($bookId, $type, $period = '14_days') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][] = [
        'book_id' => $bookId,
        'type' => $type,
        'period' => $period
    ];
}

function getCart($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $items = [];
    foreach ($_SESSION['cart'] as $item) {
        $book = getBook($pdo, $item['book_id']);
        if ($book) {
            $book['cart_type'] = $item['type'];
            $book['cart_period'] = $item['period'];
            $items[] = $book;
        }
    }
    return $items;
}

function clearCart() {
    unset($_SESSION['cart']);
}

function createRental($pdo, $userId, $bookId, $type, $period) {
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+14 days"));
    
    if ($period === '1_month') {
        $endDate = date('Y-m-d', strtotime("+1 month"));
    } elseif ($period === '3_months') {
        $endDate = date('Y-m-d', strtotime("+3 months"));
    }
    
    $book = getBook($pdo, $bookId);
    $totalPrice = $type === 'purchase' ? $book['price'] : $book['price'] * 0.3;
    
    $stmt = $pdo->prepare("INSERT INTO rentals (user_id, book_id, type, rental_period, start_date, end_date, total_price, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    return $stmt->execute([$userId, $bookId, $type, $period, $startDate, $endDate, $totalPrice]);
}

function getUserRentals($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT r.*, b.title, b.cover_image, 
                           CASE WHEN r.end_date < CURDATE() THEN 'overdue' ELSE r.status END as current_status
                           FROM rentals r
                           JOIN books b ON r.book_id = b.id
                           WHERE r.user_id = ?
                           ORDER BY r.created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllRentals($pdo) {
    $stmt = $pdo->query("SELECT r.*, u.name as user_name, u.email, b.title 
                         FROM rentals r
                         JOIN users u ON r.user_id = u.id
                         JOIN books b ON r.book_id = b.id
                         ORDER BY r.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateRentalStatus($pdo, $rentalId, $status) {
    $stmt = $pdo->prepare("UPDATE rentals SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $rentalId]);
}

// ============================================
// ПРОВЕРКА ПРОСРОЧКИ
// ============================================

function checkOverdueRentals($pdo) {
    $stmt = $pdo->prepare("SELECT r.*, u.email, u.name, b.title 
                           FROM rentals r
                           JOIN users u ON r.user_id = u.id
                           JOIN books b ON r.book_id = b.id
                           WHERE r.status = 'active' AND r.end_date < CURDATE()");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function sendOverdueNotification($pdo, $rentalId, $userId) {
    $stmt = $pdo->prepare("SELECT id FROM overdue_notifications WHERE rental_id = ? AND user_id = ?");
    $stmt->execute([$rentalId, $userId]);
    if ($stmt->fetch()) {
        return false;
    }
    
    $stmt = $pdo->prepare("INSERT INTO overdue_notifications (rental_id, user_id) VALUES (?, ?)");
    return $stmt->execute([$rentalId, $userId]);
}

function getOverdueNotifications($pdo) {
    $stmt = $pdo->prepare("SELECT on.*, u.email, u.name, b.title 
                           FROM overdue_notifications on
                           JOIN users u ON on.user_id = u.id
                           JOIN rentals r ON on.rental_id = r.id
                           JOIN books b ON r.book_id = b.id
                           ORDER BY on.sent_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>