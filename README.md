# Bookstore Project - Fall 2025

A complete online bookstore web application built with PHP and MySQL.

## Features

- **Book Catalog**: Browse books by category or search
- **User Authentication**: Registration and login system
- **Shopping Cart**: Add books to cart and manage quantities
- **Order Management**: Place orders and track order history
- **Admin Panel**: Manage books, orders, and customers
- **Reviews**: Customers can review books

## Installation

1. **Prerequisites**
   - XAMPP (or any PHP/MySQL server)
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. **Database Setup**
   - Start XAMPP and ensure MySQL is running
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema from `database/schema.sql`
   - Or run the SQL file directly in MySQL

3. **Configuration**
   - Update database credentials in `config/database.php` if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'bookstore');
     ```

4. **File Structure**
   ```
   Bookstore/
   ├── assets/
   │   ├── css/
   │   │   └── style.css
   │   └── js/
   │       └── main.js
   ├── config/
   │   └── database.php
   ├── database/
   │   └── schema.sql
   ├── includes/
   │   ├── functions.php
   │   ├── header.php
   │   └── footer.php
   ├── admin/
   │   └── (admin panel files)
   ├── index.php
   ├── books.php
   ├── book-details.php
   ├── search.php
   ├── login.php
   ├── register.php
   ├── cart.php
   └── README.md
   ```

5. **Access the Application**
   - Place the project folder in `C:\xampp\htdocs\Bookstore`
   - Access via: `http://localhost/Bookstore`
   - Admin panel: `http://localhost/Bookstore/admin/login.php`

**For detailed setup instructions, see [SETUP_GUIDE.md](SETUP_GUIDE.md)**

## Default Admin Credentials

- Email: `admin@bookstore.com`
- Password: `admin123` (change this in production!)

## Database Schema

The database includes the following tables:
- `categories` - Book categories (Science, Art, Religion, History, Geography)
- `publishers` - Publisher information (name, address, telephone)
- `authors` - Book authors
- `books` - Book information (with threshold for auto-ordering)
- `book_authors` - Junction table for multiple authors per book
- `book_orders` - Orders from publishers (with auto-creation trigger)
- `customers` - Customer accounts
- `orders` - Customer orders
- `order_items` - Order line items
- `cart` - Shopping cart items
- `reviews` - Book reviews
- `admin_users` - Admin accounts

**Database Triggers:**
- `before_book_stock_update` - Prevents negative stock quantities
- `after_book_stock_update` - Auto-creates publisher orders when stock drops below threshold
- `after_book_order_confirm` - Auto-adds stock when publisher order is confirmed

## Implemented Features

- [x] Checkout process with credit card validation
- [x] Order history page for customers
- [x] User profile page with password change
- [x] Admin panel (books management, orders management)
- [x] Publisher order management
- [x] System reports (sales, top customers, top books)
- [x] Advanced search (by ISBN, title, author, publisher, category)
- [x] Database triggers (prevent negative stock, auto-order placement)
- [x] Shopping cart management
- [x] Multiple authors per book support

## Notes

- This is a basic implementation. Enhance security, add validation, and implement missing features as needed.
- Change default admin password before deploying to production.
- Add proper error handling and logging.
- Implement CSRF protection for forms.
- Add input validation and sanitization throughout.

## License

This project is created for educational purposes.

