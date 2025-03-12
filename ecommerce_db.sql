-- Complete Database Structure for E-commerce Project

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

-- Table structure for table `tbl_user`
CREATE TABLE IF NOT EXISTS `tbl_user` (
  `tbl_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tbl_user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `tbl_admin`
CREATE TABLE IF NOT EXISTS `tbl_admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin account
INSERT INTO `tbl_admin` (`username`, `password`, `email`) 
VALUES ('admin', 'admin123', 'admin@dripforge.com');

-- Table structure for table `categories`
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default categories
INSERT INTO `categories` (`category_name`, `description`) VALUES
('fashion', 'Urban Elite Collection - Premium streetwear for the bold'),
('sneakers', 'Sneaker Series - Limited edition footwear'),
('accessories', 'Drip Accessories - Complete your look');

-- Table structure for table `products`
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_url` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample products for Fashion category
INSERT INTO `products` (`category_id`, `category_name`, `product_name`, `price`, `description`, `stock`, `image_url`) VALUES
(1, 'fashion', 'Overhold', 15.99, 'Premium streetwear for the bold', 100, 'assets2/boi1.jpg'),
(1, 'fashion', 'Ronstring', 22.99, 'Urban fashion for everyday wear', 75, 'assets2/gorl1.jpg'),
(1, 'fashion', 'Solarbreeze', 24.99, 'Lightweight and comfortable', 50, 'assets2/boi2.jpg'),
(1, 'fashion', 'Voltsillam', 18.99, 'Modern design with classic elements', 60, 'assets2/gorl2.jpg'),
(1, 'fashion', 'Regale', 10.99, 'Premium quality materials', 45, 'assets2/boi3.jpg'),
(1, 'fashion', 'Prestira', 30.99, 'Unique patterns and designs', 35, 'assets2/gorl3.jpg'),
(1, 'fashion', 'Sovrano', 22.99, 'Contemporary urban style', 40, 'assets2/boi4.jpg'),
(1, 'fashion', 'Opulenze', 20.99, 'Elegant and sophisticated', 55, 'assets2/gorl4.jpg');

-- Insert sample products for Sneakers category
INSERT INTO `products` (`category_id`, `category_name`, `product_name`, `price`, `description`, `stock`, `image_url`) VALUES
(2, 'sneakers', 'Overhold', 15.99, 'Limited edition footwear', 80, 'assets3/snea1.jpg'),
(2, 'sneakers', 'Ronstring', 22.99, 'Comfortable athletic design', 65, 'assets3/snea2.jpg'),
(2, 'sneakers', 'Solarbreeze', 24.99, 'Lightweight performance sneakers', 45, 'assets3/snea3.jpg'),
(2, 'sneakers', 'Voltsillam', 18.99, 'Urban street style', 70, 'assets3/snea4.jpg'),
(2, 'sneakers', 'Regale', 10.99, 'Classic design with modern touch', 55, 'assets3/snea5.jpg'),
(2, 'sneakers', 'Prestira', 30.99, 'Premium materials and comfort', 40, 'assets3/snea6.jpg'),
(2, 'sneakers', 'Sovrano', 22.99, 'Stylish and durable', 35, 'assets3/snea7.jpg'),
(2, 'sneakers', 'Opulenze', 20.99, 'Trendy design for active lifestyle', 50, 'assets3/snea8.jpg');

-- Insert sample products for Accessories category
INSERT INTO `products` (`category_id`, `category_name`, `product_name`, `price`, `description`, `stock`, `image_url`) VALUES
(3, 'accessories', 'Overhold', 15.99, 'Complete your look with style', 90, 'assets4/dip1.jpg'),
(3, 'accessories', 'Ronstring', 22.99, 'Essential accessories for any outfit', 85, 'assets4/dip2.jpg'),
(3, 'accessories', 'Solarbreeze', 24.99, 'Quality craftsmanship', 60, 'assets4/dip3.jpg'),
(3, 'accessories', 'Voltsillam', 18.99, 'Trendy and versatile', 75, 'assets4/drip4.jpg'),
(3, 'accessories', 'Regale', 10.99, 'Add a touch of elegance', 65, 'assets4/dip5.jpg'),
(3, 'accessories', 'Prestira', 30.99, 'Premium materials and design', 45, 'assets4/dip6.jpg'),
(3, 'accessories', 'Sovrano', 22.99, 'Stand out from the crowd', 50, 'assets4/dip7.jpg'),
(3, 'accessories', 'Opulenze', 20.99, 'Luxury accessories for any occasion', 60, 'assets4/dip8.jpg');

-- Table structure for table `cart`
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`tbl_user_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `favorites`
CREATE TABLE IF NOT EXISTS `favorites` (
  `favorite_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`favorite_id`),
  UNIQUE KEY `user_product` (`user_id`, `product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`tbl_user_id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `orders`
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `order_status` varchar(20) NOT NULL DEFAULT 'processing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`tbl_user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `order_items`
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;