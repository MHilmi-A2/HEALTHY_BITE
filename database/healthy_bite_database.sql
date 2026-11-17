CREATE DATABASE IF NOT EXISTS healthy_bite
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE healthy_bite;

CREATE TABLE IF NOT EXISTS tb_admin (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tb_admin_username (username),
    UNIQUE KEY uq_tb_admin_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tb_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('Aktif', 'Non-aktif') NOT NULL DEFAULT 'Aktif',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tb_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tb_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    photo VARCHAR(255) DEFAULT NULL,
    categories VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    calories INT UNSIGNED NOT NULL DEFAULT 0,
    description TEXT,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tb_orders (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    status ENUM('Menunggu Konfirmasi', 'Diproses', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu Konfirmasi',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tb_orders_user (user_id),
    KEY idx_tb_orders_created (created_at),
    CONSTRAINT fk_tb_orders_user FOREIGN KEY (user_id) REFERENCES tb_users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tb_order_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    menu_name VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_tb_order_items_order (order_id),
    KEY idx_tb_order_items_menu (menu_id),
    CONSTRAINT fk_tb_order_items_order FOREIGN KEY (order_id) REFERENCES tb_orders (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tb_order_items_menu FOREIGN KEY (menu_id) REFERENCES tb_categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO tb_categories (photo, categories, price, calories, description, is_available)
SELECT 'GH6.png', 'Bulking Meal Plan', 22000, 1400, 'Menu sehat lengkap untuk mendukung kebutuhan energi harian.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tb_categories WHERE categories = 'Bulking Meal Plan');
INSERT INTO tb_categories (photo, categories, price, calories, description, is_available)
SELECT 'MG1.png', 'Bulking Meal Plan Pro', 30000, 1800, 'Paket premium dengan porsi dan nutrisi lebih lengkap.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tb_categories WHERE categories = 'Bulking Meal Plan Pro');
INSERT INTO tb_categories (photo, categories, price, calories, description, is_available)
SELECT 'GH3.png', 'Bulking Meal Plan Lite', 22000, 1200, 'Pilihan ringan untuk pola makan seimbang.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tb_categories WHERE categories = 'Bulking Meal Plan Lite');
INSERT INTO tb_categories (photo, categories, price, calories, description, is_available)
SELECT 'GH5.png', 'Bulking Meal Plan Max', 22000, 1600, 'Paket makanan sehat dengan porsi ekstra.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tb_categories WHERE categories = 'Bulking Meal Plan Max');
INSERT INTO tb_categories (photo, categories, price, calories, description, is_available)
SELECT 'GH4.png', 'Bulking Meal Plan Supreme', 22000, 2000, 'Paket lengkap untuk target kalori lebih tinggi.', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM tb_categories WHERE categories = 'Bulking Meal Plan Supreme');
