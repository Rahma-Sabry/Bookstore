<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path for assets (works from both root and admin folders)
$base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $base_path = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Bookstore</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="<?php echo $base_path; ?>index.php">
                        <h1>📚 Bookstore</h1>
                    </a>
                </div>
                <nav class="main-nav">
                    <ul>
                        <?php if (strpos($_SERVER['PHP_SELF'], '/admin/') === false): ?>
                            <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                            <li><a href="<?php echo $base_path; ?>books.php">All Books</a></li>
                            <li><a href="<?php echo $base_path; ?>search.php">Search</a></li>
                            <?php if (isLoggedIn()): ?>
                                <li><a href="<?php echo $base_path; ?>cart.php">Cart</a></li>
                                <li><a href="<?php echo $base_path; ?>orders.php">My Orders</a></li>
                                <li><a href="<?php echo $base_path; ?>profile.php">Profile</a></li>
                                <li><a href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a href="<?php echo $base_path; ?>login.php">Login</a></li>
                                <li><a href="<?php echo $base_path; ?>register.php">Register</a></li>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                                <li><a href="<?php echo $base_path; ?>admin/index.php">Admin Panel</a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a href="<?php echo $base_path; ?>index.php">Store Home</a></li>
                            <li><a href="index.php">Admin Dashboard</a></li>
                            <li><a href="books.php">Manage Books</a></li>
                            <li><a href="orders.php">Customer Orders</a></li>
                            <li><a href="publisher-orders.php">Publisher Orders</a></li>
                            <li><a href="reports.php">Reports</a></li>
                            <li><a href="<?php echo $base_path; ?>logout.php">Logout</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">

