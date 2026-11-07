DROP DATABASE IF EXISTS CakeCraft;
CREATE DATABASE CakeCraft CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CakeCraft;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS order_updates;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS riders;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS fillings;
DROP TABLE IF EXISTS sizes;
DROP TABLE IF EXISTS cakes;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE users 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    id_name VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE admins 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE login_attempts 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    scope VARCHAR(10) NOT NULL,
    ident VARCHAR(100) NOT NULL,
    ip VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempts_ident (scope, ident, created_at),
    INDEX idx_attempts_ip (scope, ip, created_at)
);
CREATE TABLE items 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL UNIQUE,
    quantity INT NOT NULL DEFAULT 0
);
CREATE TABLE cakes 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    cake_name VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE fillings 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    filling_name VARCHAR(100) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE sizes 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    size_name VARCHAR(50) NOT NULL UNIQUE,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00
);
CREATE TABLE riders 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_name VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE orders 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cake_details TEXT NOT NULL,
    address TEXT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    rider_id INT NULL,
    rider_status VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_orders_status (status),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (rider_id) REFERENCES riders(id)
);
CREATE TABLE order_updates 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    update_text VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$mCOqQ4y.D2o19SndlRopq.oI.8CX1zZjVuYAJW7eole3NILjY0QWC');
INSERT INTO cakes (cake_name, price) VALUES
('Chocolate Truffle', 800.00),
('Vanilla Bean', 700.00),
('Red Velvet', 900.00),
('Black Forest', 850.00),
('Strawberry Shortcake', 750.00);
INSERT INTO fillings (filling_name, price) VALUES
('Vanilla', 100.00),
('Chocolate', 120.00),
('Strawberry', 130.00);
INSERT INTO sizes (size_name, price) VALUES
('Small', 300.00),
('Medium', 600.00),
('Large', 1000.00);