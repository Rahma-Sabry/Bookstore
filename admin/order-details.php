<?php
/**
 * Admin - Order Details Page
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

// Get order details
$order = $conn->query("SELECT o.*, c.first_name, c.last_name, c.email, c.phone 
                       FROM orders o 
                       JOIN customers c ON o.customer_id = c.customer_id 
                       WHERE o.order_id = $order_id")->fetch_assoc();

if (!$order) {
    redirect('orders.php');
}

// Get order items
$items_sql = "SELECT oi.*, b.title, b.isbn,
              GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
              FROM order_items oi
              JOIN books b ON oi.book_id = b.book_id
              LEFT JOIN book_authors ba ON b.book_id = ba.book_id
              LEFT JOIN authors a ON ba.author_id = a.author_id
              WHERE oi.order_id = ?
              GROUP BY oi.order_item_id";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "Order Details #" . $order_id;
include '../includes/header.php';
?>

<div class="container">
    <div style="margin-bottom: 1rem;">
        <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
    </div>
    
    <h1>Order Details #<?php echo $order_id; ?></h1>
    
    <div class="order-details-container">
        <div class="order-info-section">
            <h2>Order Information</h2>
            <table class="info-table">
                <tr>
                    <th>Order ID:</th>
                    <td><?php echo $order['order_id']; ?></td>
                </tr>
                <tr>
                    <th>Order Date:</th>
                    <td><?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
                <tr>
                    <th>Payment Method:</th>
                    <td><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="customer-info-section">
            <h2>Customer Information</h2>
            <table class="info-table">
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <th>Shipping Address:</th>
                    <td><?php echo nl2br(htmlspecialchars($order['shipping_address'] ?? 'N/A')); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="order-items-section">
        <h2>Order Items</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Book Title</th>
                    <th>ISBN</th>
                    <th>Authors</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6">No items found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['isbn'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($item['authors'] ?? 'N/A'); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
.order-details-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.order-info-section,
.customer-info-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.order-items-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table th {
    text-align: left;
    padding: 0.5rem 1rem 0.5rem 0;
    color: #2c3e50;
    font-weight: bold;
    width: 40%;
}

.info-table td {
    padding: 0.5rem 0;
    color: #555;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 4px;
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

.btn-secondary {
    background-color: #95a5a6;
    color: white;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 4px;
    display: inline-block;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

@media (max-width: 768px) {
    .order-details-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

