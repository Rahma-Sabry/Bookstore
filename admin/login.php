<?php
/**
 * Admin Login Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (isAdmin()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT admin_id, username, password_hash, full_name, email FROM admin_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        closeDBConnection($conn);
    } else {
        $error = 'Please fill in all fields';
    }
}

$page_title = "Admin Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Bookstore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="auth-page">
            <div class="auth-form-container">
                <h1>Admin Login</h1>
                
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="email">Email <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required autofocus>
                        <small style="color: #7f8c8d; font-size: 0.85rem;">Default: admin@bookstore.com</small>
                    </div>
                    <div class="form-group">
                        <label for="password">Password <span style="color: red;">*</span></label>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <small style="color: #7f8c8d; font-size: 0.85rem;">Default: admin123</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 4px; font-size: 0.9rem;">
                    <strong>Default Credentials:</strong><br>
                    Email: <code>admin@bookstore.com</code><br>
                    Password: <code>admin123</code>
                </div>
                
                <p class="auth-link" style="margin-top: 1rem;">
                    <a href="../index.php">Back to Store</a> | 
                    <a href="fix-admin-password.php" style="color: #e74c3c;">Fix Password</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

