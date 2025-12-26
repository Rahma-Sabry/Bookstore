# Admin Login Instructions

## Default Admin Credentials

- **Email:** `admin@bookstore.com`
- **Password:** `admin123`
- **Login URL:** `http://localhost/Bookstore/admin/login.php`

## ⚠️ Important Notes

1. **Admin login uses EMAIL address!**
   - The admin login form asks for "Email"
   - Use your email address: `admin@bookstore.com`
   - Password: `admin123`

2. **If you can't login:**
   - Make sure you're using the **email** field
   - Email: `admin@bookstore.com`
   - Password: `admin123`

## Fix Password Issues

If the default password doesn't work:

### Option 1: Use Fix Script (Easiest)
1. Go to: `http://localhost/Bookstore/admin/fix-admin-password.php`
2. The script will reset the password to `admin123`
3. **IMPORTANT:** Delete `fix-admin-password.php` after use for security!

### Option 2: Manual SQL Fix
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `bookstore` database
3. Go to `admin_users` table
4. Click "Edit" on the admin user
5. Run this SQL to set password to "admin123":
   ```sql
   UPDATE admin_users 
   SET password_hash = '$2y$10$ZCxTZWh91vrOyAWyFuf6EuKqNBoKJhTx.GJGL8xVcK2d4h6NzF1ee' 
   WHERE email = 'admin@bookstore.com';
   ```

### Option 3: Create New Admin via SQL
```sql
INSERT INTO admin_users (username, password_hash, email, full_name) 
VALUES ('admin', '$2y$10$ZCxTZWh91vrOyAWyFuf6EuKqNBoKJhTx.GJGL8xVcK2d4h6NzF1ee', 'admin@bookstore.com', 'Administrator');
```

## Verify Admin User Exists

Run this SQL in phpMyAdmin to check:
```sql
SELECT username, email, full_name FROM admin_users WHERE email = 'admin@bookstore.com';
```

You should see:
- username: admin
- email: admin@bookstore.com
- full_name: Administrator

## Common Issues

### "Invalid email or password"
- ✅ Check you're using **email**: `admin@bookstore.com`
- ✅ Check password: `admin123` (case sensitive)
- ✅ Verify admin user exists in database
- ✅ Try the fix script above

### "User not found"
- The admin user might not exist
- Re-import the database schema
- Or create admin user using Option 3 above

### Still having issues?
1. Check database connection in `config/database.php`
2. Verify `bookstore` database exists
3. Verify `admin_users` table exists
4. Check if admin user row exists in the table

