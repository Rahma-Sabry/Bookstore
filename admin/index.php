<?php
/**
 * Admin Panel - Dashboard
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();

// Get statistics
$total_books = $conn->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM book_orders WHERE status = 'pending'")->fetch_assoc()['count'];

$page_title = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="container">
    <h1>Admin Dashboard</h1>
    
    <div class="admin-stats">
        <div class="stat-card">
            <h3>Total Books</h3>
            <p class="stat-number"><?php echo $total_books; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p class="stat-number"><?php echo $total_orders; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Customers</h3>
            <p class="stat-number"><?php echo $total_customers; ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending Publisher Orders</h3>
            <p class="stat-number"><?php echo $pending_orders; ?></p>
        </div>
    </div>
    
    <div class="admin-menu">
        <h2>Admin Functions</h2>
        <ul class="admin-links">
            <li><a href="books.php?action=add" class="btn btn-primary">Add New Book</a></li>
            <li><a href="books.php" class="btn btn-primary">Manage Books</a></li>
            <li><a href="orders.php" class="btn btn-primary">Manage Customer Orders</a></li>
            <li><a href="publisher-orders.php" class="btn btn-primary">Manage Publisher Orders</a></li>
            <li><a href="reports.php" class="btn btn-primary">System Reports</a></li>
            <li><a href="test-all-pages.php" class="btn btn-secondary">Test All Pages</a></li>
        </ul>
    </div>
</div>

<style>
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: #667eea;
    margin: 0.5rem 0;
}

.admin-menu {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.admin-links {
    list-style: none;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.admin-links li {
    margin: 0;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

