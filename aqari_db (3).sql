-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 04:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aqari_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `property_id`, `created_at`) VALUES
(7, 5, 31, '2025-12-16 10:41:41'),
(8, 1, 32, '2025-12-17 06:21:39'),
(11, 1, 22, '2025-12-17 06:21:54');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `images` text NOT NULL,
  `owner` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `payment` varchar(50) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) NOT NULL,
  `purpose` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `size` int(11) DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT 0,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `type`, `price`, `address`, `lat`, `lng`, `description`, `images`, `owner`, `phone`, `payment`, `video`, `created_at`, `status`, `purpose`, `user_id`, `size`, `views`, `province`, `city`) VALUES
(15, 'شقة للبيع', 95000, 'nablus', 0.0000000, 0.0000000, '95,000$\r\nالغرف: 3\r\nالحمامات: 2\r\nالمطبخ: 12 م²\r\nالصالون: 25 م²\r\nالطابق: 2\r\n', '[\"uploads\\/6941230eabf11.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:40:24', '', '', 7, 200, 3, 'طولكرم', 'عنبتا'),
(17, 'شقة للإيجار', 85000, 'قلقيلية ', 0.0000000, 0.0000000, 'الغرف = 3\r\nالحمامات: 2\r\nالمطبخ: 14 م²\r\nالصالون: 30 م²\r\nالطابق: 3\r\n', '[\"uploads\\/69412450899ce.webp\",\"uploads\\/6941245089c3b.webp\"]', 'اسيد', '0594338617', 'كاش', '', '2025-12-20 14:46:32', '', '', 7, 150, 0, 'قلقيلية', 'قلقيلية'),
(18, 'شقة للبيع', 97000, 'جنين', 32.4074473, 35.2789879, 'السعر: 75,000$\r\nالغرف: 3\r\nالحمامات: 2\r\nالمطبخ: غير مذكور\r\nالصالون: غير مذكور\r\nالطابق: 4\r\n', '[\"uploads\\/694124d85b07c.webp\",\"uploads\\/694124d85b333.webp\"]', 'mohammed', '0599338617', 'كاش', '', '2025-12-20 14:35:20', '', '', 7, 190, 1, 'جنين', 'قباطية'),
(19, 'شقة للإيجار', 250000, '', 32.1132312, 35.0759983, 'السعر: 90,000$\r\nالغرف: 4\r\nالحمامات: 3\r\nالمطبخ: 15 م²\r\nالصالون: 35 م²\r\nالطابق: دوبلكس\r\n', '[\"uploads\\/69412a4fb6513.webp\",\"uploads\\/69412a4fb6688.webp\"]', 'islam', '0594338617', 'كاش', '', '2025-12-20 14:47:16', '', '', 7, 250, 1, 'سلفيت', 'بديا'),
(20, 'شقة للبيع', 75000, '', 32.1752674, 35.0599480, 'الحمامات: 1\r\nالمطبخ: 9 م²\r\nالصالون: 18 م²\r\nالطابق: 2\r\n', '[\"uploads\\/69412ad01e35a.webp\",\"uploads\\/69412ad01e52f.webp\"]', 'mohammed', '0594338617', 'كاش', '', '2025-12-20 14:37:21', '', '', 7, 170, 1, 'قلقيلية', 'عزون'),
(21, 'شقة للإيجار', 87000, '', 31.9695843, 35.1938438, 'الغرف 3\r\nالحمامات: 2\r\nالمطبخ: 11 م²\r\nالصالون: 22 م²\r\nالطابق: 3\r\n', '[\"uploads\\/69412bda8ca13.webp\",\"uploads\\/69412bda8cb96.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:47:26', '', '', 7, 120, 1, 'رام الله والبيرة', 'بيرزيت'),
(22, 'شقة للبيع', 97000, '', 32.4754828, 35.2341843, 'الغرف: 4\r\nالحمامات: 3\r\nالمطبخ: 16 م²\r\nالصالون: 40 م²\r\nالطابق: 5\r\n', '[\"uploads\\/69412c9dabf42.webp\",\"uploads\\/69412c9dac55d.webp\"]', 'mohammed', '0598682445', 'كاش', '', '2025-12-20 14:41:04', '', '', 7, 170, 5, 'جنين', 'اليامون'),
(23, 'شقة للإيجار', 130000, 'nablus', 0.0000000, 0.0000000, 'الغرف: 3\r\nالحمامات: 2\r\nالمطبخ: 14 م²\r\nالصالون: 28 م²\r\nالطابق: 2\r\n', '[\"uploads\\/69412ce543bd4.webp\",\"uploads\\/69412ce543e7f.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:47:08', '', '', 7, 200, 9, 'نابلس', 'عصيرة الشمالية'),
(24, 'أرض للبيع', 500000, '', 32.2249730, 35.2270603, '\r\nالسعر: 500,000 شيكل\r\nالمساحة: 800 م²\r\nالنوع: سكنية\r\n', '[\"uploads\\/69412d84959f9.webp\",\"uploads\\/69412d8495b45.webp\"]', 'mohammed', '0598682445', 'كاش', '', '2025-12-20 14:42:20', '', '', 7, 800, 0, 'نابلس', 'رفيديا'),
(25, 'أرض للبيع', 120000, 'رام الله_ بير زيت ', 31.9691748, 35.1949596, 'السعر: 120,000$\r\nالمساحة: 1000 م²\r\nالنوع: سكنية\r\n', '[\"uploads\\/69412e2510d2c.webp\",\"uploads\\/69412e2510fd4.webp\"]', 'mohammed', '0599338617', 'كاش', '', '2025-12-20 14:42:41', '', '', 7, 1000, 0, 'رام الله والبيرة', 'بيرزيت'),
(26, 'أرض للبيع', 300000, '', 31.5070394, 35.0773481, 'السعر: 300,000 شيكل\r\nالمساحة: 600 م²\r\nالنوع: زراعية\r\n', '[\"uploads\\/69412eb3b0ca6.webp\",\"uploads\\/69412eb3b0f12.webp\"]', 'mohammed', '0594338617', 'كاش', '', '2025-12-18 12:04:42', '', '', 7, 600, 4, 'الخليل', 'الخليل'),
(27, 'أرض للبيع', 2500000, 'جنين', 32.4815016, 35.2313519, 'السعر: 250,000 شيكل\r\nالمساحة: 700 م²\r\nالنوع: سكنية\r\n', '[\"uploads\\/69412f04a8e1a.webp\",\"uploads\\/69412f04a90c8.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:43:04', '', '', 7, 700, 0, 'جنين', 'اليامون'),
(28, 'أرض للبيع', 950000, '', 31.7135105, 35.1832008, 'الخضر\r\nالسعر: 95,000$\r\nالمساحة: 900 م²\r\nالنوع: سكنية\r\n', '[\"uploads\\/69412f89c3530.webp\"]', 'mohammed', '0599338617', 'كاش', '', '2025-12-20 14:46:08', '', '', 7, 900, 0, 'بيت لحم', 'بيت جالا'),
(29, 'أرض للبيع', 280000, '', 32.3103216, 35.0269032, 'السعر: 280,000 شيكل\r\nالمساحة: 750 م²\r\nالنوع: زراعية\r\n', '[\"uploads\\/6941301cd1a92.webp\",\"uploads\\/6941301cd1d1d.webp\"]', 'mohammed', '0598682445', 'كاش', '', '2025-12-20 14:43:25', '', '', 7, 750, 1, 'طولكرم', 'طولكرم'),
(30, 'أرض للبيع', 260000, '', 32.3322263, 35.1117039, 'السعر: 280,000 شيكل\r\nالمساحة: 750 م²\r\nالنوع: زراعية\r\n', '[\"uploads\\/694130626017f.webp\",\"uploads\\/694130626040c.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:44:23', '', '', 7, 650, 2, 'طولكرم', 'بلعا'),
(31, 'أرض للبيع', 110000, '', 32.2510851, 35.2604595, 'السعر: 110,000$\r\nالمساحة: 1200 م²\r\nالنوع: سكنية\r\n', '[\"uploads\\/694130a19e957.webp\",\"uploads\\/694130a19eaaa.webp\"]', 'mohammed', '0599822067', 'تقسيط', '', '2025-12-20 14:44:43', '', '', 7, 1200, 7, 'نابلس', 'عصيرة الشمالية'),
(32, 'أرض للبيع', 90000, '', 31.9658253, 35.1705837, 'السعر: 90,000$\r\nالمساحة: 1000 م²\r\nالنوع: زراعية\r\n', '[\"uploads\\/6941312b6d4fc.webp\",\"uploads\\/6941312b6d7f0.webp\",\"uploads\\/6941312b6da50.webp\"]', 'mohammed', '0599822067', 'كاش', '', '2025-12-20 14:45:13', '', '', 7, 1000, 9, 'رام الله والبيرة', 'أبو شخيدم');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `reset_token` varchar(25) NOT NULL,
  `reset_expire` datetime DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.jpg',
  `bio` text DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `last_login`, `reset_token`, `reset_expire`, `avatar`, `bio`) VALUES
(1, 'islam', 'islamzaher15@gmail.com', '$2y$10$ny2HuTuIANeG/Cz8a5JNTOsoLVMNNKRgjoQavc5e4bxkoIdwOD3m6', 'admin', '2025-12-08 18:47:01', '2025-12-20 16:41:22', '995b4e04342e0961a78fad1ec', '2025-12-19 23:43:28', 'uploads/1765951855_photo.jpg', 'صاحب موقع عقاري'),
(2, 'islam', 'the.king.gamer656@gmail.com', '$2y$10$bv.6xDK6heb0v7RVvfMZaOi/02nhvHgexsOxOjAM4gEklIyJEtchi', 'user', '2025-12-08 18:50:36', '2025-12-08 20:51:19', '', NULL, 'default.jpg', ''),
(5, 'ahmad', 'ahmad@gmail.com', '$2y$10$wckuuGUfoGivmThaL7B31.H6Ewk3TcDqyyJxhAEi6wO5qaBWXiNUy', 'user', '2025-12-13 11:35:40', '2025-12-16 20:36:44', '', NULL, 'uploads/1765741968_wallpaperflare.com_wallpaper.jpg', 'بيع عقارات بافضل الاسعار'),
(7, 'mohammad', 'mohammad@gmail.com', '$2y$10$u/fw1mRQu4yqPrH6f9gVFulMLNmjvKaPk8GiWKxGRgzEpb80aCzlC', 'user', '2025-12-16 09:02:25', '2025-12-16 11:03:16', '', NULL, 'default.jpg', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
