<?php
/**
 * Admin - Manage Customer Orders
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$message = '';
$error = '';

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    if ($order_id > 0) {
        // Update order status from pending to processing
        $stmt = $conn->prepare("UPDATE orders SET status = 'processing' WHERE order_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = 'Order confirmed successfully! Status updated to processing.';
            } else {
                $error = 'Order could not be confirmed. It may already be processed or cancelled.';
            }
        } else {
            $error = 'Failed to confirm order.';
        }
    } else {
        $error = 'Invalid order ID.';
    }
}

// Get all customer orders
$sql = "SELECT o.*, c.first_name, c.last_name, c.email
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        ORDER BY o.order_date DESC";
$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$page_title = "Customer Orders";
include '../includes/header.php';
?>

<div class="container">
    <h1>Customer Orders</h1>
    
    <?php if ($message): ?>
        <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Order Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8">No orders found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-small">View Details</a>
                            <?php if ($order['status'] == 'pending'): ?>
                                <form method="POST" style="display: inline; margin-left: 0.5rem;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="confirm_order" class="btn btn-small btn-success" onclick="return confirm('Confirm this order? Status will be updated to processing.');">Confirm Order</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.admin-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin-top: 1rem;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.admin-table th,
.admin-table td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.admin-table th {
    background-color: #2c3e50;
    color: white;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: bold;
    display: inline-block;
}

.status-pending {
    background-color: #f39c12;
    color: white;
}

.status-processing {
    background-color: #3498db;
    color: white;
}

.status-shipped {
    background-color: #9b59b6;
    color: white;
}

.status-delivered {
    background-color: #27ae60;
    color: white;
}

.status-cancelled {
    background-color: #e74c3c;
    color: white;
}

.btn-small {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}

.btn-small:hover {
    opacity: 0.9;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-success:hover {
    background-color: #229954;
}

.success-message {
    background-color: #27ae60;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.error-message {
    background-color: #e74c3c;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

