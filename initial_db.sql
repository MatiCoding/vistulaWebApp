CREATE DATABASE ecommerce_supermarket;
USE ecommerce_supermarket;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    remember_token VARCHAR(255) NULL,
    token_expiry TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    category_id INT NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'shipped', 'cancelled') DEFAULT 'pending',
    shipping_address VARCHAR(1000) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order details table
CREATE TABLE order_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES
('Fruits & Vegetables', 'Fresh produce'),
('Dairy', 'Milk, cheese, yogurt'),
('Meat', 'Various meats'),
('Beverages', 'Drinks and juices');

-- Insert admin user
INSERT INTO users (full_name, email, password, is_admin) VALUES
('Admin', 'admin@store.com', '$2y$10$ddExKFdFaM/FE6VF3BcCpuBU7KWFtGX/CPioGGy.Cdr/DhRnNpnS2', TRUE);
-- Password: admin123

-- Insert sample products
INSERT INTO products (name, description, price, stock, category_id) VALUES
('Apple', 'Fresh red apples', 2.50, 50, 1),
('Whole Milk', '1 liter milk', 1.80, 100, 2),
('Chicken Breast', '1kg chicken breast', 5.99, 30, 3),
('Orange Juice', '500ml natural juice', 2.00, 60, 4);
