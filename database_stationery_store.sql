CREATE DATABASE IF NOT EXISTS stationery_store;

-- Use the database
USE stationery_store;

-- Categories Table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(255),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cart Table
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    shipping_address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

INSERT INTO categories (category_name) VALUES 
('Pens & Writing'),
('Notebooks & Journals'),
('Art Supplies'),
('Office Supplies'),
('Desk Accessories');

INSERT INTO products (product_name, description, price, stock_quantity, category_id) VALUES
('Gel Pen Set', 'Pack of 10 smooth gel pens', 15.90, 50, 1),
('Hardcover Notebook', 'A5 dotted journal, 200 pages', 24.90, 30, 2),
('Watercolor Set', '24-color professional watercolor', 45.00, 20, 3),
('Desk Organizer', 'Wooden 5-compartment organizer', 39.90, 15, 5);

-- ==========================================
-- Online Stationery Store
-- ==========================================

-- 1. USERS TABLE
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- 2. CART TABLE
CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id),

    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
);


-- 3. ORDERS TABLE
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    status VARCHAR(50) DEFAULT 'Pending',
    shipping_address TEXT,

    FOREIGN KEY (user_id)
        REFERENCES users(user_id)
);


-- 4. ORDER ITEMS TABLE
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (order_id)
        REFERENCES orders(order_id),

    FOREIGN KEY (product_id)
        REFERENCES products(product_id)
);

-- ==================================================
-- CREATE CONTACT MESSAGES TABLE
-- ==================================================

CREATE TABLE IF NOT EXISTS contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    admin_notes TEXT NULL
);

-- ==================================================
-- OPTIONAL: ADD INDEXES FOR BETTER PERFORMANCE
-- ==================================================

ALTER TABLE contact_messages ADD INDEX idx_email (email);
ALTER TABLE contact_messages ADD INDEX idx_status (status);
ALTER TABLE contact_messages ADD INDEX idx_submitted_at (submitted_at);

-- ==================================================
-- SAMPLE INSERT DATA (OPTIONAL)
-- ==================================================

INSERT INTO contact_messages (name, email, message, status) VALUES
('John Doe', 'john@example.com', 'I love your stationery products! Do you have any discounts for students?', 'read'),
('Jane Smith', 'jane@example.com', 'When will the new notebooks be in stock?', 'unread'),
('Ahmad Bin Abdullah', 'ahmad@example.com', 'I want to return an item. What is the return policy?', 'replied'),
('Siti Nurhaliza', 'siti@example.com', 'Your website is very user-friendly. Keep up the good work!', 'unread'),
('Tan Wei Ming', 'tan@example.com', 'Do you offer international shipping to Singapore?', 'read');