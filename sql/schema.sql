
-- DragonStone schema
CREATE DATABASE IF NOT EXISTS dragonstone CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dragonstone;

-- Users
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  email VARCHAR(150) UNIQUE,
  phone VARCHAR(20) UNIQUE,
  password VARCHAR(255),
  role ENUM('customer','admin','manager','analyst') DEFAULT 'customer',
  eco_points INT DEFAULT 0,
  login_attempts INT DEFAULT 0,
  last_attempt TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(150) UNIQUE,
  title VARCHAR(150)
);

-- Products
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sku VARCHAR(80) UNIQUE,
  title VARCHAR(255),
  category_id INT,
  price DECIMAL(10,2),
  currency VARCHAR(10) DEFAULT 'ZAR',
  stock INT DEFAULT 0,
  short_desc VARCHAR(512),
  long_desc TEXT,
  image VARCHAR(255),
  carbon_material DECIMAL(6,3) DEFAULT 0,
  carbon_manufacture DECIMAL(6,3) DEFAULT 0,
  carbon_packaging DECIMAL(6,3) DEFAULT 0,
  carbon_shipping_per_km DECIMAL(6,6) DEFAULT 0.0000,
  subscription_available TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Orders & items
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  subtotal DECIMAL(10,2),
  shipping DECIMAL(10,2),
  tax DECIMAL(10,2),
  total DECIMAL(10,2),
  status VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  qty INT,
  price DECIMAL(10,2),
  carbon_kg DECIMAL(6,3),
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Subscriptions
DROP TABLE IF EXISTS subscriptions;
CREATE TABLE subscriptions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  product_id INT,
  interval_months INT,
  next_charge DATE,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Remember Me tokens
DROP TABLE IF EXISTS remember_tokens;
CREATE TABLE remember_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  token VARCHAR(64) UNIQUE,
  expires TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- EcoPoints ledger
DROP TABLE IF EXISTS ecopoints_ledger;
CREATE TABLE ecopoints_ledger (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  points INT,
  reason VARCHAR(255),
  reference_id VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts (community)
DROP TABLE IF EXISTS posts;
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(255),
  body TEXT,
  upvotes INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_created ON orders(created_at);
