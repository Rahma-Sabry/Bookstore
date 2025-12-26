<?php
/**
 * Checkout Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$customer_id = $_SESSION['customer_id'];

// Get cart items
$sql = "SELECT c.*, b.title, b.price, b.stock_quantity
        FROM cart c
        JOIN books b ON c.book_id = b.book_id
        WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cart_items)) {
    redirect('cart.php');
}

// Get customer info
$customer = $conn->query("SELECT * FROM customers WHERE customer_id = $customer_id")->fetch_assoc();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping_address = sanitizeInput($_POST['shipping_address'] ?? '');
    $payment_method = sanitizeInput($_POST['payment_method'] ?? '');
    $credit_card_number = sanitizeInput($_POST['credit_card_number'] ?? '');
    $credit_card_expiry = sanitizeInput($_POST['credit_card_expiry'] ?? '');
    
    $error = '';
    
    // Validate credit card if payment method is credit/debit card
    if ($payment_method == 'Credit Card' || $payment_method == 'Debit Card') {
        // Remove spaces and dashes from credit card number
        $credit_card_number = preg_replace('/[\s-]/', '', $credit_card_number);
        
        // Validate credit card number (basic validation - 13-19 digits)
        if (!preg_match('/^\d{13,19}$/', $credit_card_number)) {
            $error = 'Invalid credit card number. Please enter a valid card number.';
        }
        
        // Validate expiry date (MM/YY or MM-YY format)
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $credit_card_expiry)) {
            $error = 'Invalid expiry date. Please use MM/YY format.';
        } else {
            // Check if card is expired
            list($month, $year) = explode('/', $credit_card_expiry);
            $expiry_date = strtotime("20{$year}-{$month}-01");
            if ($expiry_date < time()) {
                $error = 'Credit card has expired.';
            }
        }
    }
    
    if (!$error && $shipping_address && $payment_method) {
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, shipping_address, payment_method, credit_card_number, credit_card_expiry) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idssss", $customer_id, $total, $shipping_address, $payment_method, $credit_card_number, $credit_card_expiry);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Create order items
            foreach ($cart_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiidd", $order_id, $item['book_id'], $item['quantity'], $item['price'], $subtotal);
                $stmt->execute();
                
                // Update stock (trigger will prevent negative stock)
                $new_stock = $item['stock_quantity'] - $item['quantity'];
                if ($new_stock >= 0) {
                    $conn->query("UPDATE books SET stock_quantity = $new_stock WHERE book_id = {$item['book_id']}");
                }
            }
            
            // Clear cart
            $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");
            
            redirect("order-success.php?order_id=$order_id");
        } else {
            $error = 'Failed to place order. Please try again.';
        }
    }
}

$page_title = "Checkout";
include 'includes/header.php';
?>

<div class="container">
    <h1>Checkout</h1>
    
    <div class="checkout-content">
        <div class="checkout-form">
            <h2>Shipping Information</h2>
            <form method="POST" action="checkout.php">
                <div class="form-group">
                    <label for="shipping_address">Shipping Address</label>
                    <textarea id="shipping_address" name="shipping_address" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" required onchange="toggleCreditCardFields()">
                        <option value="">Select Payment Method</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="Debit Card">Debit Card</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Cash on Delivery">Cash on Delivery</option>
                    </select>
                </div>
                
                <div id="credit-card-fields" style="display: none;">
                    <div class="form-group">
                        <label for="credit_card_number">Credit Card Number *</label>
                        <input type="text" id="credit_card_number" name="credit_card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                    </div>
                    <div class="form-group">
                        <label for="credit_card_expiry">Expiry Date (MM/YY) *</label>
                        <input type="text" id="credit_card_expiry" name="credit_card_expiry" placeholder="12/25" maxlength="5">
                    </div>
                </div>
                
                <?php if (isset($error) && $error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-success btn-large">Place Order</button>
            </form>
        </div>
        
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['title']); ?> x <?php echo $item['quantity']; ?></span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-total">
                <strong>Total: $<?php echo number_format($total, 2); ?></strong>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

.checkout-form,
.order-summary {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.summary-items {
    margin: 1rem 0;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.summary-total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 2px solid #2c3e50;
    font-size: 1.2rem;
    text-align: right;
}

@media (max-width: 768px) {
    .checkout-content {
        grid-template-columns: 1fr;
    }
}

.error-message {
    background-color: #e74c3c;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin: 1rem 0;
}
</style>

<script>
function toggleCreditCardFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const creditCardFields = document.getElementById('credit-card-fields');
    const creditCardNumber = document.getElementById('credit_card_number');
    const creditCardExpiry = document.getElementById('credit_card_expiry');
    
    if (paymentMethod === 'Credit Card' || paymentMethod === 'Debit Card') {
        creditCardFields.style.display = 'block';
        creditCardNumber.required = true;
        creditCardExpiry.required = true;
    } else {
        creditCardFields.style.display = 'none';
        creditCardNumber.required = false;
        creditCardExpiry.required = false;
        creditCardNumber.value = '';
        creditCardExpiry.value = '';
    }
}

// Format credit card number
document.getElementById('credit_card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s/g, '');
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
});

// Format expiry date
document.getElementById('credit_card_expiry')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});
</script>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

