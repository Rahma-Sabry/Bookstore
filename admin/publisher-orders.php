<?php
/**
 * Admin - Manage Publisher Orders
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
        // Update order status to confirmed (trigger will add stock)
        $stmt = $conn->prepare("UPDATE book_orders SET status = 'confirmed', confirmed_date = NOW() WHERE order_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            $message = 'Order confirmed successfully! Stock has been updated.';
        } else {
            $error = 'Failed to confirm order.';
        }
    }
}

// Get all publisher orders
$sql = "SELECT bo.*, b.title, b.isbn, p.publisher_name
        FROM book_orders bo
        JOIN books b ON bo.book_id = b.book_id
        JOIN publishers p ON bo.publisher_id = p.publisher_id
        ORDER BY bo.order_date DESC";
$orders = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

$page_title = "Publisher Orders";
include '../includes/header.php';
?>

<div class="container">
    <h1>Publisher Orders</h1>
    
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
                <th>Book</th>
                <th>ISBN</th>
                <th>Publisher</th>
                <th>Quantity</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Confirmed Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="9">No orders found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['title']); ?></td>
                        <td><?php echo htmlspecialchars($order['isbn'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($order['publisher_name']); ?></td>
                        <td><?php echo $order['order_quantity']; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $order['confirmed_date'] ? date('Y-m-d H:i', strtotime($order['confirmed_date'])) : 'N/A'; ?></td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <button type="submit" name="confirm_order" class="btn btn-small btn-success" onclick="return confirm('Confirm this order? Stock will be updated automatically.');">Confirm</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Confirmed</span>
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
}

.status-pending {
    background-color: #f39c12;
    color: white;
}

.status-confirmed {
    background-color: #27ae60;
    color: white;
}

.status-cancelled {
    background-color: #e74c3c;
    color: white;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-success:hover {
    background-color: #229954;
}

.text-muted {
    color: #7f8c8d;
}

.btn-small {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    border-radius: 4px;
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

