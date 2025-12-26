<?php
/**
 * Customer Order History Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$customer_id = $_SESSION['customer_id'];

// Get all orders for this customer
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
        FROM orders o
        WHERE o.customer_id = ?
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "My Orders";
include 'includes/header.php';
?>

<div class="container">
    <h1>My Orders</h1>
    
    <?php if (empty($orders)): ?>
        <p>You haven't placed any orders yet. <a href="books.php">Browse books</a> to get started!</p>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Order #<?php echo $order['order_id']; ?></h3>
                            <p class="order-date">Placed on <?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
                        <p><strong>Items:</strong> <?php echo $order['item_count']; ?> item(s)</p>
                        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?></p>
                        <?php if ($order['shipping_address']): ?>
                            <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-items">
                        <h4>Order Items:</h4>
                        <?php
                        $items_sql = "SELECT oi.*, b.title, b.isbn, a.author_name
                                      FROM order_items oi
                                      JOIN books b ON oi.book_id = b.book_id
                                      LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                                      LEFT JOIN authors a ON ba.author_id = a.author_id
                                      WHERE oi.order_id = ?
                                      GROUP BY oi.order_item_id";
                        $items_stmt = $conn->prepare($items_sql);
                        $items_stmt->bind_param("i", $order['order_id']);
                        $items_stmt->execute();
                        $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        ?>
                        <ul>
                            <?php foreach ($items as $item): ?>
                                <li>
                                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                    <?php if ($item['isbn']): ?>
                                        (ISBN: <?php echo htmlspecialchars($item['isbn']); ?>)
                                    <?php endif; ?>
                                    - Quantity: <?php echo $item['quantity']; ?> - 
                                    $<?php echo number_format($item['subtotal'], 2); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.orders-list {
    margin-top: 2rem;
}

.order-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #eee;
}

.order-date {
    color: #7f8c8d;
    margin: 0.5rem 0 0 0;
}

.order-details {
    margin: 1rem 0;
}

.order-details p {
    margin: 0.5rem 0;
}

.order-items {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.order-items ul {
    list-style: none;
    padding: 0;
    margin: 0.5rem 0;
}

.order-items li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: bold;
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
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

