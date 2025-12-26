<?php
/**
 * Customer Profile Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$customer_id = $_SESSION['customer_id'];
$message = '';
$error = '';

// Get customer info
$customer = $conn->query("SELECT * FROM customers WHERE customer_id = $customer_id")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = sanitizeInput($_POST['first_name'] ?? '');
    $last_name = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    $password = $_POST['password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($first_name && $last_name && $email) {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ? AND customer_id != ?");
        $stmt->bind_param("si", $email, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email already registered by another user.';
        } else {
            // Update password if provided
            if ($password && $new_password && $confirm_password) {
                // Verify current password
                if (password_verify($password, $customer['password_hash'])) {
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) >= 6) {
                            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, shipping_address = ?, password_hash = ? WHERE customer_id = ?");
                            $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $shipping_address, $password_hash, $customer_id);
                        } else {
                            $error = 'New password must be at least 6 characters long.';
                        }
                    } else {
                        $error = 'New passwords do not match.';
                    }
                } else {
                    $error = 'Current password is incorrect.';
                }
            } else {
                // Update without password
                $stmt = $conn->prepare("UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, shipping_address = ? WHERE customer_id = ?");
                $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $shipping_address, $customer_id);
            }
            
            if (!isset($error) || $error == '') {
                if ($stmt->execute()) {
                    $_SESSION['customer_name'] = $first_name . ' ' . $last_name;
                    $message = 'Profile updated successfully!';
                    // Refresh customer data
                    $customer = $conn->query("SELECT * FROM customers WHERE customer_id = $customer_id")->fetch_assoc();
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

$page_title = "My Profile";
include 'includes/header.php';
?>

<div class="container">
    <h1>My Profile</h1>
    
    <?php if ($message): ?>
        <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="profile.php" class="profile-form">
        <h2>Personal Information</h2>
        
        <div class="form-group">
            <label for="first_name">First Name *</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name *</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="shipping_address">Shipping Address</label>
            <textarea id="shipping_address" name="shipping_address" rows="3"><?php echo htmlspecialchars($customer['shipping_address'] ?? ''); ?></textarea>
        </div>
        
        <h2>Change Password</h2>
        <p class="form-note">Leave blank if you don't want to change your password.</p>
        
        <div class="form-group">
            <label for="password">Current Password</label>
            <input type="password" id="password" name="password">
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" minlength="6">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="6">
        </div>
        
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>

<style>
.profile-form {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 2rem auto;
}

.profile-form h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #2c3e50;
}

.profile-form h2:first-child {
    margin-top: 0;
}

.form-note {
    color: #7f8c8d;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

