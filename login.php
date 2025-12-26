<?php
/**
 * Login Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT customer_id, first_name, last_name, password_hash FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['customer_id'] = $user['customer_id'];
                $_SESSION['customer_name'] = $user['first_name'] . ' ' . $user['last_name'];
                redirect('index.php');
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

$page_title = "Login";
include 'includes/header.php';
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-form-container">
            <h1>Login</h1>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

<style>
.auth-page {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
}

.auth-form-container {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.error-message {
    background-color: #e74c3c;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.btn-block {
    width: 100%;
}

.auth-link {
    text-align: center;
    margin-top: 1rem;
}

.auth-link a {
    color: #667eea;
    text-decoration: none;
}
</style>

<?php include 'includes/footer.php'; ?>

