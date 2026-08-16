CREATE DATABASE cosmetic_shop;
USE cosmetic_shop;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image_url VARCHAR(500),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Customers table
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shipping_address TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

-- Order items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Promotions table
CREATE TABLE promotions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    valid_until DATE,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert sample data
INSERT INTO categories (name, description) VALUES
('Skin Care', 'Premium skin care products for all skin types'),
('Lip Gloss', 'Shiny and moisturizing lip gloss varieties'),
('Baby Cosmetics', 'Gentle and safe cosmetics for babies'),
('Makeup', 'Various makeup products and accessories'),
('Fragrances', 'Luxury perfumes and body sprays');

INSERT INTO products (name, description, price, category_id, stock_quantity) VALUES
('Hydrating Face Cream', 'Deep moisturizing cream for dry skin', 45.99, 1, 50),
('Vitamin C Serum', 'Anti-aging serum with vitamin C', 65.50, 1, 30),
('Shimmer Lip Gloss', 'Sparkling lip gloss with vanilla flavor', 22.99, 2, 100),
('Matte Lip Gloss', 'Non-sticky matte finish lip gloss', 25.99, 2, 80),
('Baby Shampoo', 'Tear-free gentle baby shampoo', 15.99, 3, 60),
('Baby Lotion', 'Hypoallergenic baby body lotion', 18.50, 3, 45),
('Foundation', 'Full coverage liquid foundation', 42.00, 4, 25),
('Eyeshadow Palette', '12-color professional eyeshadow', 55.99, 4, 35),
('Floral Perfume', 'Long-lasting floral fragrance', 89.99, 5, 20);

INSERT INTO promotions (code, description, discount_percent, min_order_amount, valid_until) VALUES
('WELCOME10', 'Get 10% off on your first order', 10.00, 30.00, '2024-12-31'),
('BEAUTY20', '20% off on orders above $100', 20.00, 100.00, '2024-11-30'),
('FREESHIP', 'Free shipping on orders above $50', 0.00, 50.00, '2024-10-31');

-- Public user activity table
CREATE TABLE public_user_activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(64) NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    activity_details TEXT,
    page_url VARCHAR(500),
    product_id INT NULL,
    order_id INT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_id (session_id),
    INDEX idx_activity_type (activity_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);
