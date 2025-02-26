-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2025 at 02:03 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jisrgp`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `CartID` int(11) NOT NULL,
  `CustomerID` int(6) UNSIGNED DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `ProductName` varchar(255) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `CraftsmanID` int(6) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`CartID`, `CustomerID`, `ProductID`, `Quantity`, `ProductName`, `Price`, `CraftsmanID`) VALUES
(125, 130, 86, 7, 'تعليقه مفتاح', 20.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `ProductID` int(11) NOT NULL,
  `UserName` varchar(255) NOT NULL,
  `CommentText` text NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `Rating` int(11) DEFAULT NULL,
  `CommentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`ProductID`, `UserName`, `CommentText`, `CreatedAt`, `Rating`, `CommentID`) VALUES
(86, 'احمد', 'لطيففف جدا وناعممم انصح بشراءه !!', '2025-02-11 23:46:20', 5, 22);

-- --------------------------------------------------------

--
-- Table structure for table `discount_codes`
--

CREATE TABLE `discount_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percentage` decimal(2,0) NOT NULL,
  `expiry_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `used_by_customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discount_codes`
--

INSERT INTO `discount_codes` (`id`, `code`, `discount_percentage`, `expiry_date`, `is_active`, `used_by_customer_id`) VALUES
(1, 'WELCOMEJISR', 10, '2025-12-31', 1, 67);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `FavoritesID` int(11) UNSIGNED NOT NULL,
  `CustomerID` int(6) UNSIGNED NOT NULL,
  `ProductID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`FavoritesID`, `CustomerID`, `ProductID`) VALUES
(4, 130, 86);

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `InvoiceID` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `CustomerID` int(11) NOT NULL,
  `InvoiceDate` datetime DEFAULT current_timestamp(),
  `Amount` decimal(10,2) DEFAULT NULL,
  `PaymentMethod` varchar(255) DEFAULT NULL,
  `PaymentStatus` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`InvoiceID`, `OrderID`, `CustomerID`, `InvoiceDate`, `Amount`, `PaymentMethod`, `PaymentStatus`) VALUES
(71, 74, 130, '2025-02-12 02:15:13', 30.00, 'كاش', 'pending'),
(72, 75, 130, '2025-02-12 18:06:47', 30.00, 'كاش', 'failed'),
(73, 78, 130, '2025-02-13 15:58:55', 45.00, 'كاش', 'pending'),
(74, 78, 130, '2025-02-13 16:03:07', 45.00, 'كاش', 'pending'),
(75, 79, 130, '2025-02-13 16:08:35', 30.00, 'فيزا', NULL),
(76, 80, 130, '2025-02-13 16:09:42', 45.00, 'فيزا', 'success'),
(77, 81, 130, '2025-02-13 16:12:27', 30.00, 'كاش', NULL),
(78, 82, 130, '2025-02-13 16:13:08', 30.00, 'كاش', NULL),
(79, 83, 130, '2025-02-13 23:16:41', 30.00, 'كاش', NULL),
(80, 84, 130, '2025-02-14 05:48:42', 145.00, 'كاش', NULL),
(81, 100, 130, '2025-02-14 05:57:45', 120.00, 'كاش', NULL),
(82, 101, 130, '2025-02-14 05:58:21', 120.00, 'كاش', NULL),
(83, 102, 130, '2025-02-14 05:59:16', 225.00, 'فيزا', NULL),
(84, 104, 130, '2025-02-14 06:03:04', 85.00, 'فيزا', NULL),
(85, 105, 130, '2025-02-14 06:03:51', 155.00, 'فيزا', NULL),
(86, 106, 130, '2025-02-14 06:06:10', 105.00, 'فيزا', NULL),
(87, 107, 130, '2025-02-14 06:26:13', 130.00, 'كاش', NULL),
(88, 108, 130, '2025-02-14 06:27:04', 200.00, 'فيزا', 'pending'),
(89, 109, 130, '2025-02-14 16:27:29', 35.00, 'فيزا', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `OrderID` int(11) NOT NULL,
  `OrderDate` datetime DEFAULT current_timestamp(),
  `ShippingStatus` varchar(255) DEFAULT NULL,
  `OrderStatus` varchar(255) DEFAULT NULL,
  `TotalAmount` decimal(10,2) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `is_new` tinyint(1) DEFAULT 1,
  `PaymentMethod` varchar(255) DEFAULT NULL,
  `escrowStatus` enum('pending','held','released','cancelled') DEFAULT 'pending',
  `CustomerID` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `OrderDate`, `ShippingStatus`, `OrderStatus`, `TotalAmount`, `Address`, `is_new`, `PaymentMethod`, `escrowStatus`, `CustomerID`) VALUES
(74, '2025-02-12 02:15:13', 'pending', 'pending', 30.00, 'السمرة', 1, 'كاش', 'held', 130),
(75, '2025-02-12 18:06:47', 'cancelled', 'cancelled', 30.00, 'السمرة', 1, 'كاش', 'cancelled', 130),
(78, '2025-02-13 15:58:55', 'pending', 'pending', 45.00, 'السمرة', 1, 'كاش', 'held', 130),
(79, '2025-02-13 16:08:35', 'Pending', 'New', 30.00, 'السمرة', 1, 'فيزا', 'pending', 130),
(80, '2025-02-13 16:09:41', 'shipped', 'accepted', 45.00, 'السمرة', 1, 'فيزا', 'released', 130),
(81, '2025-02-13 16:12:26', 'Pending', 'New', 30.00, 'السمرة', 1, 'كاش', 'pending', 130),
(82, '2025-02-13 16:13:08', 'Pending', 'New', 30.00, 'السمرة', 1, 'كاش', 'pending', 130),
(83, '2025-02-13 23:16:41', 'Pending', 'New', 30.00, 'السمرة', 1, 'كاش', 'pending', 130),
(84, '2025-02-14 05:48:42', 'Pending', 'New', 145.00, 'السمرة', 1, 'كاش', 'pending', 130),
(100, '2025-02-14 05:57:45', 'Pending', 'Cancelled', 120.00, 'السمرة', 1, 'كاش', 'pending', 130),
(101, '2025-02-14 05:58:21', 'Pending', 'Cancelled', 120.00, 'السمرة', 1, 'كاش', 'pending', 130),
(102, '2025-02-14 05:59:16', 'Pending', 'New', 225.00, 'السمرة', 1, 'فيزا', 'pending', 130),
(104, '2025-02-14 06:03:04', 'Pending', 'New', 85.00, 'السمرة', 1, 'فيزا', 'pending', 130),
(105, '2025-02-14 06:03:51', 'Pending', 'New', 155.00, 'السمرة', 1, 'فيزا', 'pending', 130),
(106, '2025-02-14 06:06:10', 'Pending', 'New', 105.00, 'السمرة', 1, 'فيزا', 'pending', 130),
(107, '2025-02-14 06:26:13', 'Pending', 'New', 130.00, 'السمرة', 1, 'كاش', 'pending', 130),
(108, '2025-02-14 06:27:04', 'pending', 'Shipped', 200.00, 'السمرة', 1, 'فيزا', 'held', 130),
(109, '2025-02-14 16:27:29', 'Pending', 'Shipped', 35.00, 'السمرة', 1, 'فيزا', 'pending', 130);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `OrderID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `CraftsmanID` int(11) DEFAULT NULL,
  `PaymentStatus` enum('pending','released','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `OrderID`, `ProductID`, `quantity`, `Price`, `CraftsmanID`, `PaymentStatus`) VALUES
(98, 74, 86, 1, 15.00, 131, 'pending'),
(99, 75, 86, 2, 15.00, 131, 'failed'),
(100, 78, 86, 2, 15.00, 131, 'pending'),
(101, 79, 86, 1, 15.00, 131, 'pending'),
(102, 80, 86, 2, 15.00, 131, 'released'),
(103, 81, 86, 1, 15.00, 131, 'pending'),
(104, 82, 86, 1, 15.00, 131, 'pending'),
(105, 83, 86, 1, 15.00, 131, 'pending'),
(107, 84, 86, 3, 20.00, 131, 'pending'),
(119, 102, 86, 3, 20.00, 131, 'pending'),
(122, 104, 86, 3, 20.00, 131, 'pending'),
(123, 105, 86, 6, 20.00, 131, 'pending'),
(124, 106, 86, 1, 20.00, 131, 'pending'),
(126, 107, 86, 4, 20.00, 131, 'pending'),
(128, 108, 86, 4, 20.00, 131, 'pending'),
(130, 109, 86, 1, 20.00, 131, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `ProductID` int(11) NOT NULL,
  `ProductName` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Category` varchar(50) DEFAULT NULL,
  `CraftsmanID` int(11) DEFAULT NULL,
  `CraftsmanFullName` varchar(255) DEFAULT NULL,
  `IsApproved` tinyint(1) DEFAULT 0,
  `RejectionReason` text DEFAULT NULL,
  `Stock` int(11) NOT NULL DEFAULT 0,
  `ProductImage` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `sales_percentage` float DEFAULT 0,
  `sales_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`ProductID`, `ProductName`, `Description`, `Price`, `Category`, `CraftsmanID`, `CraftsmanFullName`, `IsApproved`, `RejectionReason`, `Stock`, `ProductImage`, `CreatedAt`, `sales_percentage`, `sales_count`) VALUES
(86, 'تعليقه مفتاح', 'نمط لطيف- شبح -علاقه مفاتيح ', 20.00, 'other', 131, 'سارة ', 1, NULL, 10, 'uploads\\علاقه مفاتيح.jpg', '2025-02-11 23:14:25', 100, 19),
(88, 'دمية فتاة', 'دمية مصنوعه من الكروشيه نمط لطيف ', 45.00, 'dolls', 131, 'سارة الراشدي', 0, 'لا يوجد سبب', 5, 'uploads/67aea1426d5f7_67a7c2da21417_67a69f8a2824f_678fa2c6212b7_678296d070920_07.jpg', '2025-02-14 01:49:54', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(6) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','craftsman','customer') NOT NULL DEFAULT 'customer',
  `craft_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `address`, `password`, `role`, `craft_description`, `created_at`, `phone_number`, `profile_picture`, `security_question`, `security_answer`) VALUES
(129, 'Admin', 'admin', 'Admin@gmail.com', 'القنفذه', '$2y$10$X/eZPUcv6itMbor6r38N2uB1WWpHeu6eMChu9tst8SY8hUW48XQyG', 'admin', NULL, '2025-02-11 23:12:14', '0508792745', 'uploads/IMG_8455 (1).PNG', 'اسم حيوانك الأليف الأول؟', '$2y$10$Vc3VqMohh7z8fQvyd6OFeOXT7q8tqzku9cr6/pBVbUJOfppO0VyVy'),
(130, 'احمد', 'الناشري', 'Ahmed@gmail.com', 'القوز', '$2y$10$KrFGgyrhvKkCbQoj8tXEKOmISKlLTRwBuAjQwKVlQhW2tHOuChVXG', 'customer', NULL, '2025-02-11 23:12:57', '0555555555', 'uploads/بروفايل الحرفي.jpg', 'ما هو اسم مدينتك المفضلة؟', '$2y$10$UKTXsQIErfYlVf2.Mm99DO2expmcezBoSJRNwkDrRMyR8tDfNwv7e'),
(131, 'سارة', 'الراشدي', 'sara@gmail.com', NULL, '$2y$10$roaBGSTny9XtU9jUChrYvOkevV2e0IKasS13EUOTy1hogZG4u3PR.', 'craftsman', 'متخصصه في الكروشيه', '2025-02-11 23:14:06', '0566666666', NULL, 'ما هو اسم مدينتك المفضلة؟', '$2y$10$yMJIOvicBATFVzvdNEwNP.TrkPLYxDiyXphVYpysfFJblfzOSGB5a');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`CartID`),
  ADD KEY `fk_product_id` (`ProductID`),
  ADD KEY `fk_customer_id` (`CustomerID`);

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`CommentID`),
  ADD KEY `comment-product` (`ProductID`);

--
-- Indexes for table `discount_codes`
--
ALTER TABLE `discount_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`FavoritesID`),
  ADD UNIQUE KEY `CustomerID` (`CustomerID`,`ProductID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`InvoiceID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `fk_customer` (`CustomerID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`ProductID`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `CartID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `CommentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `discount_codes`
--
ALTER TABLE `discount_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `FavoritesID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `InvoiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_customer_id` FOREIGN KEY (`CustomerID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product_id` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment-product` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `in-or` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`CustomerID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pr-orit` FOREIGN KEY (`ProductID`) REFERENCES `product` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
