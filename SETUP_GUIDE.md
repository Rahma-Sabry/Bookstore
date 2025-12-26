# Bookstore Project - Complete Setup Guide

## Step-by-Step Installation Instructions

### Step 1: Prerequisites

Ensure you have the following installed on your system:

1. **XAMPP** (or any PHP/MySQL server)
   - Download from: https://www.apachefriends.org/
   - Includes: Apache, MySQL, PHP, phpMyAdmin
   - **Minimum Requirements:**
     - PHP 7.4 or higher
     - MySQL 5.7 or higher

2. **Web Browser** (Chrome, Firefox, Edge, etc.)

---

### Step 2: Install XAMPP

1. Download XAMPP from the official website
2. Run the installer
3. Install to default location: `C:\xampp`
4. Complete the installation

---

### Step 3: Place Project Files

1. **Copy the project folder** to XAMPP's htdocs directory:
   ```
   C:\xampp\htdocs\Bookstore
   ```

2. **Verify the folder structure:**
   ```
   Bookstore/
   ├── admin/
   ├── assets/
   ├── config/
   ├── database/
   ├── includes/
   ├── index.php
   ├── login.php
   ├── register.php
   └── ... (other files)
   ```

---

### Step 4: Start XAMPP Services

1. **Open XAMPP Control Panel:**
   - Go to Start Menu → Search "XAMPP Control Panel"
   - Or navigate to: `C:\xampp\xampp-control.exe`

2. **Start the following services:**
   - Click **Start** button for **Apache**
   - Click **Start** button for **MySQL**
   
   ✅ Both services should show "Running" status (green)

---

### Step 5: Create the Database

You have **two options** to create the database:

#### Option A: Using phpMyAdmin (Recommended for beginners)

1. **Open phpMyAdmin:**
   - Open your web browser
   - Go to: `http://localhost/phpmyadmin`

2. **Create Database:**
   - Click on "New" in the left sidebar
   - Database name: `bookstore`
   - Collation: `utf8mb4_general_ci` (optional)
   - Click "Create"

3. **Import Schema:**
   - Select the `bookstore` database from the left sidebar
   - Click on the "Import" tab at the top
   - Click "Choose File" button
   - Navigate to: `C:\xampp\htdocs\Bookstore\database\schema.sql`
   - Click "Go" button at the bottom
   - Wait for "Import has been successfully finished" message

#### Option B: Using MySQL Command Line

1. **Open Command Prompt** (as Administrator)

2. **Navigate to MySQL bin directory:**
   ```cmd
   cd C:\xampp\mysql\bin
   ```

3. **Login to MySQL:**
   ```cmd
   mysql -u root -p
   ```
   (Press Enter if no password is set)

4. **Run the SQL file:**
   ```sql
   source C:/xampp/htdocs/Bookstore/database/schema.sql
   ```

5. **Verify database creation:**
   ```sql
   USE bookstore;
   SHOW TABLES;
   ```
   You should see all tables listed.

6. **Exit MySQL:**
   ```sql
   exit;
   ```

---

### Step 6: Configure Database Connection (If Needed)

1. **Open the database configuration file:**
   ```
   C:\xampp\htdocs\Bookstore\config\database.php
   ```

2. **Check/Update these values** (usually default is fine):
   ```php
   define('DB_HOST', 'localhost');    // Usually 'localhost'
   define('DB_USER', 'root');         // Usually 'root'
   define('DB_PASS', '');             // Usually empty for XAMPP
   define('DB_NAME', 'bookstore');    // Database name
   ```

3. **If you changed MySQL root password:**
   - Update `DB_PASS` with your MySQL password

---

### Step 7: Access the Application

1. **Open your web browser**

2. **Navigate to:**
   ```
   http://localhost/Bookstore
   ```

3. **You should see the bookstore homepage!** 🎉

---

### Step 8: Test the Application

#### Test Customer Features:

1. **Register a new account:**
   - Click "Register" in the navigation
   - Fill in the registration form
   - Click "Register"
   - You'll be redirected to login

2. **Login:**
   - Use your registered email and password
   - Click "Login"

3. **Browse Books:**
   - Click "All Books" or browse by category
   - Click on a book to see details

4. **Add to Cart:**
   - Click "Add to Cart" on any book
   - View cart by clicking "Cart" in navigation

5. **Checkout:**
   - Go to Cart
   - Click "Proceed to Checkout"
   - Fill in shipping address
   - Select payment method
   - If Credit/Debit Card: Enter card number and expiry (MM/YY)
   - Click "Place Order"

6. **View Orders:**
   - Click "My Orders" in navigation
   - See your order history

7. **Edit Profile:**
   - Click "Profile" in navigation
   - Update your information
   - Change password if needed

#### Test Admin Features:

1. **Admin Login:**
   - Go to: `http://localhost/Bookstore/admin/login.php`
   - **Default Credentials:**
     - Email: `admin@bookstore.com`
     - Password: `admin123`

2. **Admin Dashboard:**
   - View statistics
   - Access all admin functions

3. **Add New Book:**
   - Click "Add New Book"
   - Fill in book details
   - Select category, publisher, authors
   - Set price, stock quantity, and threshold
   - Click "Add Book"

4. **Manage Books:**
   - Click "Manage Books"
   - View all books
   - Click "Edit" to modify a book

5. **Publisher Orders:**
   - Click "Manage Publisher Orders"
   - View pending orders (auto-created when stock drops below threshold)
   - Click "Confirm" to add stock

6. **System Reports:**
   - Click "System Reports"
   - View various reports:
     - Sales for previous month
     - Sales for specific date
     - Top 5 Customers
     - Top 10 Selling Books
     - Order count for specific book

---

### Step 9: Verify Database Triggers

The system has automatic triggers that should work:

1. **Test Negative Stock Prevention:**
   - Try to update a book's stock to negative value
   - Should show error (trigger prevents it)

2. **Test Auto-Order Creation:**
   - Edit a book and reduce stock below threshold
   - Check "Publisher Orders" - a new pending order should appear

3. **Test Order Confirmation:**
   - Confirm a publisher order
   - Check book stock - it should increase automatically

---

### Troubleshooting

#### Problem: "Connection failed" error

**Solution:**
- Check if MySQL is running in XAMPP Control Panel
- Verify database credentials in `config/database.php`
- Make sure database `bookstore` exists

#### Problem: "Table doesn't exist" error

**Solution:**
- Re-import the `database/schema.sql` file
- Make sure you selected the `bookstore` database before importing

#### Problem: Page shows blank or errors

**Solution:**
- Check Apache is running in XAMPP Control Panel
- Check PHP error logs: `C:\xampp\php\logs\php_error_log`
- Enable error display in PHP (for development only)

#### Problem: Can't access admin panel

**Solution:**
- Make sure you're logged in as admin
- URL should be: `http://localhost/Bookstore/admin/`
- Check file permissions

#### Problem: Triggers not working

**Solution:**
- Verify triggers were created: In phpMyAdmin, go to database → Structure → Check for triggers
- Re-import schema if triggers are missing
- Check MySQL version (triggers require MySQL 5.0+)

---

### Default Login Credentials

#### Admin Account:
- **URL:** `http://localhost/Bookstore/admin/login.php`
- **Username:** `admin`
- **Password:** `admin123`

#### Customer Account:
- Create your own account via registration page
- Or use any email/password you register

---

### Important Notes

1. **Security:**
   - ⚠️ Change admin password before production use
   - The default password is for development only

2. **File Permissions:**
   - Ensure PHP has read/write permissions
   - On Windows, usually no issues

3. **Port Conflicts:**
   - If port 80 is busy, Apache might use port 8080
   - Access via: `http://localhost:8080/Bookstore`

4. **Database Backup:**
   - Regularly backup your database
   - Use phpMyAdmin → Export feature

---

### Project Structure

```
Bookstore/
├── admin/                    # Admin panel files
│   ├── login.php            # Admin login
│   ├── index.php            # Admin dashboard
│   ├── books.php            # Book management
│   ├── orders.php           # Customer orders
│   ├── publisher-orders.php   # Publisher orders
│   └── reports.php          # System reports
├── assets/                  # Static files
│   ├── css/
│   └── js/
├── config/
│   └── database.php         # Database configuration
├── database/
│   └── schema.sql          # Database schema
├── includes/
│   ├── functions.php       # Common functions
│   ├── header.php          # Page header
│   └── footer.php          # Page footer
├── index.php               # Homepage
├── books.php               # Books listing
├── book-details.php        # Book details page
├── search.php              # Search page
├── login.php               # Customer login
├── register.php            # Customer registration
├── profile.php             # Customer profile
├── cart.php                # Shopping cart
├── checkout.php            # Checkout page
├── orders.php              # Order history
├── order-success.php       # Order confirmation
└── logout.php              # Logout
```

---

### Next Steps After Setup

1. ✅ Test all features
2. ✅ Add more sample data (books, authors, publishers)
3. ✅ Customize the design (CSS files)
4. ✅ Add more books through admin panel
5. ✅ Test the complete order flow
6. ✅ Review and test all reports

---

### Support

If you encounter any issues:

1. Check the troubleshooting section above
2. Verify all steps were completed correctly
3. Check XAMPP error logs
4. Verify database connection
5. Ensure all files are in correct locations

---

**Happy Coding! 🚀**

