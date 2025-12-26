<?php
/**
 * Admin - System Reports
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();

// Get report data
$report_type = $_GET['report'] ?? 'sales_month';

// Total sales for previous month
$prev_month_sales = 0;
$prev_month_query = "SELECT SUM(total_amount) as total FROM orders 
                     WHERE MONTH(order_date) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH))
                     AND YEAR(order_date) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))";
$result = $conn->query($prev_month_query);
if ($result && $row = $result->fetch_assoc()) {
    $prev_month_sales = $row['total'] ?? 0;
}

// Sales for specific date
$date_sales = 0;
$selected_date = $_GET['date'] ?? date('Y-m-d');
if ($selected_date) {
    $date_query = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = ?";
    $stmt = $conn->prepare($date_query);
    $stmt->bind_param("s", $selected_date);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $date_sales = $row['total'] ?? 0;
    }
}

// Top 5 Customers (Last 3 Months)
$top_customers = [];
$top_customers_query = "SELECT c.customer_id, c.first_name, c.last_name, c.email, SUM(o.total_amount) as total_purchases
                         FROM customers c
                         JOIN orders o ON c.customer_id = o.customer_id
                         WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                         GROUP BY c.customer_id
                         ORDER BY total_purchases DESC
                         LIMIT 5";
$top_customers = $conn->query($top_customers_query)->fetch_all(MYSQLI_ASSOC);

// Top 10 Selling Books (Last 3 Months)
$top_books = [];
$top_books_query = "SELECT b.book_id, b.title, b.isbn, SUM(oi.quantity) as total_sold
                    FROM books b
                    JOIN order_items oi ON b.book_id = oi.book_id
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE o.order_date >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                    GROUP BY b.book_id
                    ORDER BY total_sold DESC
                    LIMIT 10";
$top_books = $conn->query($top_books_query)->fetch_all(MYSQLI_ASSOC);

// Total number of times a specific book has been ordered
$book_order_count = 0;
$selected_book_id = $_GET['book_id'] ?? 0;
if ($selected_book_id > 0) {
    $count_query = "SELECT COUNT(*) as count FROM book_orders WHERE book_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $selected_book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $book_order_count = $row['count'] ?? 0;
    }
}

// Get all books for selection
$all_books = $conn->query("SELECT book_id, title, isbn FROM books ORDER BY title")->fetch_all(MYSQLI_ASSOC);

$page_title = "System Reports";
include '../includes/header.php';
?>

<div class="container">
    <h1>System Reports</h1>
    
    <div class="reports-section">
        <h2>1. Total Sales for Previous Month</h2>
        <div class="report-box">
            <p class="report-value">$<?php echo number_format($prev_month_sales, 2); ?></p>
            <p class="report-label">Total sales for <?php echo date('F Y', strtotime('first day of last month')); ?></p>
        </div>
    </div>
    
    <div class="reports-section">
        <h2>2. Total Sales for a Specific Date</h2>
        <form method="GET" action="reports.php" class="report-form">
            <input type="hidden" name="report" value="sales_date">
            <div class="form-group">
                <label for="date">Select Date:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                <button type="submit" class="btn btn-primary">Get Sales</button>
            </div>
        </form>
        <?php if ($selected_date): ?>
            <div class="report-box">
                <p class="report-value">$<?php echo number_format($date_sales, 2); ?></p>
                <p class="report-label">Total sales for <?php echo date('F d, Y', strtotime($selected_date)); ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="reports-section">
        <h2>3. Top 5 Customers (Last 3 Months)</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Total Purchases</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_customers)): ?>
                    <tr>
                        <td colspan="4">No data available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($top_customers as $index => $customer): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td>$<?php echo number_format($customer['total_purchases'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="reports-section">
        <h2>4. Top 10 Selling Books (Last 3 Months)</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Book Title</th>
                    <th>ISBN</th>
                    <th>Total Copies Sold</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_books)): ?>
                    <tr>
                        <td colspan="4">No data available.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($top_books as $index => $book): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                            <td><?php echo $book['total_sold']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="reports-section">
        <h2>5. Total Number of Times a Specific Book Has Been Ordered</h2>
        <form method="GET" action="reports.php" class="report-form">
            <input type="hidden" name="report" value="book_orders">
            <div class="form-group">
                <label for="book_id">Select Book:</label>
                <select id="book_id" name="book_id" required>
                    <option value="">Select a book</option>
                    <?php foreach ($all_books as $book): ?>
                        <option value="<?php echo $book['book_id']; ?>" <?php echo ($selected_book_id == $book['book_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($book['title'] . ' (' . ($book['isbn'] ?? 'N/A') . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">Get Count</button>
            </div>
        </form>
        <?php if ($selected_book_id > 0): ?>
            <div class="report-box">
                <p class="report-value"><?php echo $book_order_count; ?></p>
                <p class="report-label">Total number of times this book has been ordered from publishers</p>
            </div>
        <?php endif; ?>
    </div>
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

.reports-section {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.report-box {
    text-align: center;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 1rem;
}

.report-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
    margin: 0.5rem 0;
}

.report-label {
    color: #7f8c8d;
    margin: 0.5rem 0;
}

.report-form {
    margin: 1rem 0;
}

.report-form .form-group {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.report-form select,
.report-form input[type="date"] {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

