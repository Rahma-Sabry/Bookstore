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
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

