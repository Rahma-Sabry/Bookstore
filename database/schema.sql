-- Bookstore Database Schema
-- Created for DB Project Fall 2025

CREATE DATABASE IF NOT EXISTS bookstore;
USE bookstore;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Publishers Table
CREATE TABLE IF NOT EXISTS publishers (
    publisher_id INT AUTO_INCREMENT PRIMARY KEY,
    publisher_name VARCHAR(200) NOT NULL,
    address TEXT,
    telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Authors Table
CREATE TABLE IF NOT EXISTS authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(200) NOT NULL,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Books Table
CREATE TABLE IF NOT EXISTS books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    publisher_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    threshold INT DEFAULT 10,
    description TEXT,
    image_url VARCHAR(255),
    publication_year YEAR,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    FOREIGN KEY (publisher_id) REFERENCES publishers(publisher_id) ON DELETE CASCADE,
    INDEX idx_title (title),
    INDEX idx_category (category_id),
    INDEX idx_publisher (publisher_id),
    INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Book Authors Junction Table (for multiple authors per book)
CREATE TABLE IF NOT EXISTS book_authors (
    book_id INT NOT NULL,
    author_id INT NOT NULL,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Book Orders Table (Orders from Publishers)
CREATE TABLE IF NOT EXISTS book_orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    publisher_id INT NOT NULL,
    order_quantity INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    confirmed_date TIMESTAMP NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (publisher_id) REFERENCES publishers(publisher_id) ON DELETE CASCADE,
    INDEX idx_book (book_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password_hash VARCHAR(255) NOT NULL,
    shipping_address TEXT,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    zip_code VARCHAR(20),
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders Table (Customer Orders)
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    payment_method VARCHAR(50),
    credit_card_number VARCHAR(20),
    credit_card_expiry VARCHAR(10),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_order_date (order_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shopping Cart Table (for logged-in users)
CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (customer_id, book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    INDEX idx_book (book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Data
INSERT INTO categories (category_name, description) VALUES
('Science', 'Science and technology books'),
('Art', 'Art and design books'),
('Religion', 'Religious and spiritual books'),
('History', 'Historical books and biographies'),
('Geography', 'Geography and travel books');

INSERT INTO publishers (publisher_name, address, telephone) VALUES
('Academic Press', '123 University Ave, New York, NY 10001', '555-0101'),
('Literary House', '456 Book Street, Boston, MA 02101', '555-0102'),
('Science Publishers Inc', '789 Research Blvd, San Francisco, CA 94101', '555-0103'),
('History Books Ltd', '321 Heritage Lane, Washington, DC 20001', '555-0104'),
('Art & Design Press', '654 Creative Way, Los Angeles, CA 90001', '555-0105');

INSERT INTO authors (author_name, bio) VALUES
('John Doe', 'Award-winning author with over 20 published novels'),
('Jane Smith', 'Renowned science writer and researcher'),
('Michael Johnson', 'Business consultant and bestselling author'),
('Sarah Williams', 'Educational content creator and author'),
('Robert Brown', 'Historian and author of multiple historical books'),
('Emily Davis', 'Art critic and author');

INSERT INTO books (isbn, title, category_id, publisher_id, price, stock_quantity, threshold, description, publication_year) VALUES
('978-1234567890', 'The Great Adventure', 4, 2, 19.99, 50, 10, 'An epic tale of adventure and discovery', 2023),
('978-1234567891', 'Science Fundamentals', 1, 3, 29.99, 30, 10, 'Comprehensive guide to modern science', 2023),
('978-1234567892', 'Business Success', 4, 1, 24.99, 40, 10, 'Strategies for business growth and success', 2023),
('978-1234567893', 'Learning Made Easy', 1, 1, 34.99, 25, 10, 'Educational guide for students', 2023),
('978-1234567894', 'World History', 4, 4, 39.99, 20, 10, 'Complete world history from ancient times', 2022),
('978-1234567895', 'Modern Art', 2, 5, 44.99, 15, 10, 'Guide to modern art movements', 2023);

-- Link books to authors
INSERT INTO book_authors (book_id, author_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6);

-- Insert default admin user (password: admin123 - should be changed in production)
-- Note: If login doesn't work, run: http://localhost/Bookstore/admin/fix-admin-password.php
INSERT INTO admin_users (username, password_hash, email, full_name) VALUES
('admin', '$2y$10$ZCxTZWh91vrOyAWyFuf6EuKqNBoKJhTx.GJGL8xVcK2d4h6NzF1ee', 'admin@bookstore.com', 'Administrator')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash);

-- Trigger to prevent negative stock quantity
DELIMITER //
CREATE TRIGGER before_book_stock_update
BEFORE UPDATE ON books
FOR EACH ROW
BEGIN
    IF NEW.stock_quantity < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock quantity cannot be negative';
    END IF;
END//
DELIMITER ;

-- Trigger to automatically place order when stock drops below threshold
DELIMITER //
CREATE TRIGGER after_book_stock_update
AFTER UPDATE ON books
FOR EACH ROW
BEGIN
    DECLARE order_qty INT DEFAULT 50; -- Constant order quantity
    
    -- Check if stock dropped from above threshold to below threshold
    IF OLD.stock_quantity >= OLD.threshold AND NEW.stock_quantity < NEW.threshold THEN
        -- Insert order only if there isn't already a pending order for this book
        IF NOT EXISTS (
            SELECT 1 FROM book_orders 
            WHERE book_id = NEW.book_id 
            AND status = 'pending'
        ) THEN
            INSERT INTO book_orders (book_id, publisher_id, order_quantity, status)
            VALUES (NEW.book_id, NEW.publisher_id, order_qty, 'pending');
        END IF;
    END IF;
END//
DELIMITER ;

-- Trigger to add stock when order is confirmed
DELIMITER //
CREATE TRIGGER after_book_order_confirm
AFTER UPDATE ON book_orders
FOR EACH ROW
BEGIN
    IF OLD.status != 'confirmed' AND NEW.status = 'confirmed' THEN
        UPDATE books 
        SET stock_quantity = stock_quantity + NEW.order_quantity
        WHERE book_id = NEW.book_id;
    END IF;
END//
DELIMITER ;

