<?php
/**
 * Fix Admin Password Script
 * Run this once to set the correct admin password
 * 
 * Usage: Access via browser: http://localhost/Bookstore/admin/fix-admin-password.php
 * Then DELETE this file for security!
 */

require_once '../config/database.php';

// Security: Only allow if not in production or add IP check
// For now, this is a one-time setup script

$conn = getDBConnection();

// Generate password hash for "admin123"
$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Update admin user
$stmt = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
$stmt->bind_param("s", $password_hash);

if ($stmt->execute()) {
    echo "<h1>✅ Admin Password Fixed!</h1>";
    echo "<p>Password has been updated successfully.</p>";
    echo "<p><strong>Email:</strong> admin@bookstore.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<hr>";
    echo "<p><a href='login.php'>Go to Admin Login</a></p>";
    echo "<p style='color: red;'><strong>⚠️ IMPORTANT: Delete this file (fix-admin-password.php) after use for security!</strong></p>";
} else {
    echo "<h1>❌ Error</h1>";
    echo "<p>Failed to update password: " . $conn->error . "</p>";
}

closeDBConnection($conn);
?>

