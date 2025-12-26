<?php
/**
 * Registration Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    
    if ($first_name && $last_name && $email && $password && $confirm_password) {
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } else {
            $conn = getDBConnection();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, email, password_hash, phone, shipping_address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $first_name, $last_name, $email, $password_hash, $phone, $shipping_address);
                
                if ($stmt->execute()) {
                    $success = 'Registration successful! You can now login.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
            closeDBConnection($conn);
        }
    } else {
        $error = 'Please fill in all required fields';
    }
}

$page_title = "Register";
include 'includes/header.php';
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-form-container">
            <h1>Register</h1>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-group">
                    <label for="shipping_address">Shipping Address (Optional)</label>
                    <textarea id="shipping_address" name="shipping_address" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

<style>
.success-message {
    background-color: #27ae60;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<?php include 'includes/footer.php'; ?>

