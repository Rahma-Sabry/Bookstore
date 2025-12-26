<?php
/**
 * Shopping Cart Page
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();

if (!isLoggedIn()) {
    redirect('login.php');
}

$conn = getDBConnection();
$customer_id = $_SESSION['customer_id'];

// Handle cart actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;
    
    if ($action == 'add' && $book_id > 0) {
        // Check if item already in cart
        $stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE customer_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $customer_id, $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update quantity
            $cart_item = $result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + 1;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $stmt->bind_param("ii", $new_quantity, $cart_item['cart_id']);
            $stmt->execute();
        } else {
            // Add new item
            $stmt = $conn->prepare("INSERT INTO cart (customer_id, book_id, quantity) VALUES (?, ?, 1)");
            $stmt->bind_param("ii", $customer_id, $book_id);
            $stmt->execute();
        }
        redirect('cart.php');
    } elseif ($action == 'remove' && $book_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND book_id = ?");
        $stmt->bind_param("ii", $customer_id, $book_id);
        $stmt->execute();
        redirect('cart.php');
    } elseif ($action == 'update' && isset($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $book_id => $quantity) {
            $book_id = (int)$book_id;
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE customer_id = ? AND book_id = ?");
                $stmt->bind_param("iii", $quantity, $customer_id, $book_id);
                $stmt->execute();
            } else {
                $stmt = $conn->prepare("DELETE FROM cart WHERE customer_id = ? AND book_id = ?");
                $stmt->bind_param("ii", $customer_id, $book_id);
                $stmt->execute();
            }
        }
        redirect('cart.php');
    }
}

// Get cart items
$sql = "SELECT c.*, b.title, b.price, b.image_url, b.stock_quantity,
        GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
        FROM cart c
        JOIN books b ON c.book_id = b.book_id
        LEFT JOIN book_authors ba ON b.book_id = ba.book_id
        LEFT JOIN authors a ON ba.author_id = a.author_id
        WHERE c.customer_id = ?
        GROUP BY c.cart_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$page_title = "Shopping Cart";
include 'includes/header.php';
?>

<div class="container">
    <h1>Shopping Cart</h1>
    
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="books.php">Browse books</a></p>
    <?php else: ?>
        <form method="POST" action="cart.php?action=update">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-item-info">
                                    <?php if ($item['image_url']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="cart-item-image">
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                                        <small>By <?php echo htmlspecialchars($item['authors'] ?? 'N/A'); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo $item['book_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>" style="width: 60px;">
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="cart.php?action=remove&book_id=<?php echo $item['book_id']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Total:</strong></td>
                        <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div class="cart-actions">
                <button type="submit" class="btn btn-primary">Update Cart</button>
                <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<style>
.cart-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin: 2rem 0;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.cart-table th,
.cart-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.cart-table th {
    background-color: #2c3e50;
    color: white;
}

.cart-item-info {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.cart-item-image {
    width: 80px;
    height: 100px;
    object-fit: cover;
    border-radius: 4px;
}

.cart-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-danger {
    background-color: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background-color: #c0392b;
}
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

