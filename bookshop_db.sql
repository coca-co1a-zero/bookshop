-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июл 18 2026 г., 15:01
-- Версия сервера: 8.0.30
-- Версия PHP: 8.0.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bookshop_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `authors`
--

CREATE TABLE `authors` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `authors`
--

INSERT INTO `authors` (`id`, `name`) VALUES
(3, 'Агата Кристи'),
(4, 'Джейн Остин'),
(2, 'Джордж Оруэлл'),
(6, 'Лев Толстой'),
(7, 'Ольга Примаченко'),
(1, 'Фёдор Достоевский'),
(5, 'Эрнест Хемингуэй');

-- --------------------------------------------------------

--
-- Структура таблицы `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author_id` int NOT NULL,
  `category_id` int NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `year` int DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT 'default.jpg',
  `status` enum('available','rented','unavailable') DEFAULT 'available',
  `stock` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `books`
--

INSERT INTO `books` (`id`, `title`, `author_id`, `category_id`, `description`, `price`, `year`, `cover_image`, `status`, `stock`, `created_at`) VALUES
(1, 'Преступление и наказание', 1, 1, 'Роман Фёдора Достоевского о студенте Раскольникове...', '350.00', 1866, 'default.jpg', 'available', 5, '2026-07-17 09:46:17'),
(2, '1984', 2, 2, 'Роман-антиутопия Джорджа Оруэлла...', '400.00', 1949, 'default.jpg', 'available', 1, '2026-07-17 09:46:17'),
(3, 'Убийство в Восточном экспрессе', 3, 3, 'Детективный роман Агаты Кристи...', '280.00', 1934, 'default.jpg', 'available', 4, '2026-07-17 09:46:17'),
(4, 'Гордость и предубеждение', 4, 1, 'Роман Джейн Остин о любви и предрассудках...', '320.00', 1813, 'default.jpg', 'available', 2, '2026-07-17 09:46:17'),
(5, 'Старик и море', 5, 1, 'Повесть Эрнеста Хемингуэя...', '250.00', 1952, 'default.jpg', 'available', 6, '2026-07-17 09:46:17'),
(6, 'Война и мир', 6, 1, 'Роман-эпопея Льва Толстого...', '450.00', 1869, 'default.jpg', 'available', 1221, '2026-07-17 09:46:17'),
(7, 'Бизнес своими руками', 7, 5, 'Как школьнику заработать первые деньги', '250.00', 2021, '1784286130_51822a784b387d82.jpg', 'available', 15, '2026-07-17 10:57:32');

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(5, 'Бизнес'),
(3, 'Детектив'),
(6, 'Детская'),
(4, 'Научно-популярная'),
(2, 'Фантастика'),
(1, 'Художественная литература');

-- --------------------------------------------------------

--
-- Структура таблицы `overdue_notifications`
--

CREATE TABLE `overdue_notifications` (
  `id` int NOT NULL,
  `rental_id` int NOT NULL,
  `user_id` int NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `rentals`
--

CREATE TABLE `rentals` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `type` enum('purchase','rental') NOT NULL,
  `rental_period` enum('14_days','1_month','3_months') DEFAULT '14_days',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','returned','overdue') DEFAULT 'active',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `rentals`
--

INSERT INTO `rentals` (`id`, `user_id`, `book_id`, `type`, `rental_period`, `start_date`, `end_date`, `status`, `total_price`, `created_at`) VALUES
(1, 2, 6, 'purchase', '14_days', '2026-07-17', '2026-07-31', 'active', '450.00', '2026-07-17 11:02:39'),
(2, 2, 2, 'rental', '1_month', '2026-07-17', '2026-08-17', 'active', '120.00', '2026-07-17 11:02:55'),
(3, 2, 2, 'purchase', '14_days', '2026-07-17', '2026-07-31', 'active', '400.00', '2026-07-17 11:04:15');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Администратор', 'admin@bookshop.local', '$2y$10$kSpv9R/VJYqkGHjjUiY4mO.MkMjM62BptqXsx5UenTrsjDuBXKEJ2', 'admin', '2026-07-17 09:46:17'),
(2, 'Test', 'test@mail.ru', '$2y$10$UoQ2sA9JoSaWzZds9jHlJ.OaAaR7iY51JGUBWPXXlhVVCJsXfMHQa', 'user', '2026-07-17 10:17:59');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `overdue_notifications`
--
ALTER TABLE `overdue_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `authors`
--
ALTER TABLE `authors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `overdue_notifications`
--
ALTER TABLE `overdue_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `books_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `overdue_notifications`
--
ALTER TABLE `overdue_notifications`
  ADD CONSTRAINT `overdue_notifications_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `overdue_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
