-- QRdoklad Database Schema
-- Vytvořeno pro inv.allimedia.cz
-- Použití: Nahrajte tento soubor do phpMyAdmin nebo spusťte přes mysql console

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Tabulka pro uživatele
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','accountant','readonly') NOT NULL DEFAULT 'readonly',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro firemní údaje
CREATE TABLE `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Česká republika',
  `ic` varchar(20) NOT NULL,
  `dic` varchar(20) DEFAULT NULL,
  `vat_payer` tinyint(1) NOT NULL DEFAULT 0,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `bank_account` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `invoice_heading_color` varchar(7) DEFAULT '#cacaca',
  `invoice_trapezoid_bg_color` varchar(7) DEFAULT '#cacaca',
  `invoice_trapezoid_text_color` varchar(7) DEFAULT '#000000',
  `invoice_labels_color` varchar(7) DEFAULT '#cacaca',
  `invoice_footer_color` varchar(7) DEFAULT '#393b41',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro klienty
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Česká republika',
  `ic` varchar(20) DEFAULT NULL,
  `dic` varchar(20) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro faktury
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `manual_client` tinyint(1) NOT NULL DEFAULT 0,
  `client_name` varchar(255) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `client_city` varchar(100) DEFAULT NULL,
  `client_zip` varchar(20) DEFAULT NULL,
  `client_country` varchar(100) DEFAULT NULL,
  `client_ic` varchar(20) DEFAULT NULL,
  `client_dic` varchar(20) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Bankovní převod',
  `status` enum('created','paid','overdue') NOT NULL DEFAULT 'created',
  `payment_date` date DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qr_payment` tinyint(1) NOT NULL DEFAULT 1,
  `show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `show_signature` tinyint(1) NOT NULL DEFAULT 1,
  `note` text DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `due_date` (`due_date`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro položky faktur
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro sledování pokusů o přihlášení
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `last_attempt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `login_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabulka pro moduly
CREATE TABLE `modules` (
  `id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `version` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `config` text DEFAULT NULL,
  `installed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vložení výchozího administrátorského účtu
-- Heslo: admin123 (ZMĚŇTE PO PRVNÍ INSTALACI!)
INSERT INTO `users` (`username`, `password`, `email`, `role`, `created_at`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'info@allimedia.cz', 'admin', NOW());

-- Nastavení AUTO_INCREMENT pro tabulky
ALTER TABLE `users` AUTO_INCREMENT = 1;
ALTER TABLE `company` AUTO_INCREMENT = 1;
ALTER TABLE `clients` AUTO_INCREMENT = 1;
ALTER TABLE `invoices` AUTO_INCREMENT = 1;
ALTER TABLE `invoice_items` AUTO_INCREMENT = 1;
ALTER TABLE `login_attempts` AUTO_INCREMENT = 1;

-- Konec SQL skriptu
SET foreign_key_checks = 1;