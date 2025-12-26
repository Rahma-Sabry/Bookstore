<?php
/**
 * Order Success Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

$conn = getDBConnection();
$customer_id = $_SESSION['customer_id'];

// Get order details
$order = $conn->query("SELECT * FROM orders WHERE order_id = $order_id AND customer_id = $customer_id")->fetch_assoc();

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

$page_title = "Order Confirmed";
include 'includes/header.php';
?>

<div class="container">
    <div class="success-page">
        <div class="success-icon">✓</div>
        <h1>Order Confirmed!</h1>
        <p class="success-message">Thank you for your order. Your order has been placed successfully.</p>
        
        <div class="order-summary-box">
            <h2>Order Summary</h2>
            <p><strong>Order Number:</strong> #<?php echo $order['order_id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>
            
            <h3>Order Items:</h3>
            <ul class="order-items-list">
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
        
        <div class="success-actions">
            <a href="orders.php" class="btn btn-primary">View All Orders</a>
            <a href="books.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
.success-page {
    text-align: center;
    padding: 3rem 0;
}

.success-icon {
    width: 80px;
    height: 80px;
    background-color: #27ae60;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    margin: 0 auto 2rem;
}

.success-message {
    font-size: 1.2rem;
    color: #7f8c8d;
    margin-bottom: 2rem;
}

.order-summary-box {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 600px;
    margin: 2rem auto;
    text-align: left;
}

.order-summary-box h2 {
    margin-top: 0;
    color: #2c3e50;
}

.order-items-list {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.order-items-list li {
    padding: 0.75rem 0;
    border-bottom: 1px solid #eee;
}

.order-items-list li:last-child {
    border-bottom: none;
}

.success-actions {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

