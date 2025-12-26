# Quick Start Guide

## 🚀 Fast Setup (5 Minutes)

### 1. Start XAMPP
- Open XAMPP Control Panel
- Start **Apache** and **MySQL**

### 2. Import Database
- Go to: `http://localhost/phpmyadmin`
- Click "New" → Database name: `bookstore` → Create
- Select `bookstore` → Click "Import" tab
- Choose file: `database/schema.sql`
- Click "Go"

### 3. Access Application
- **Customer Site:** `http://localhost/Bookstore`
- **Admin Panel:** `http://localhost/Bookstore/admin/login.php`

### 4. Login Credentials
**Admin:**
- Email: `admin@bookstore.com`
- Password: `admin123`

**Customer:**
- Register a new account

---

## 📋 Quick Checklist

- [ ] XAMPP installed
- [ ] Apache running
- [ ] MySQL running
- [ ] Database imported
- [ ] Project in `C:\xampp\htdocs\Bookstore`
- [ ] Can access homepage
- [ ] Can login as admin

---

## 🔗 Important URLs

| Page | URL |
|------|-----|
| Homepage | `http://localhost/Bookstore` |
| Admin Login | `http://localhost/Bookstore/admin/login.php` |
| Customer Login | `http://localhost/Bookstore/login.php` |
| Register | `http://localhost/Bookstore/register.php` |

---

## ⚙️ Configuration

**File:** `config/database.php`

```php
DB_HOST: 'localhost'
DB_USER: 'root'
DB_PASS: '' (empty for XAMPP default)
DB_NAME: 'bookstore'
```

---

## 🐛 Common Issues

**"Connection failed"**
→ Check MySQL is running

**"Table doesn't exist"**
→ Re-import schema.sql

**Blank page**
→ Check Apache is running

---

**For detailed instructions, see [SETUP_GUIDE.md](SETUP_GUIDE.md)**

