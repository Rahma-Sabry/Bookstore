<?php
/**
 * Logout Page
 */

require_once 'config/database.php';

session_start();

// Clear cart for logged-in user
if (isset($_SESSION['customer_id'])) {
    $conn = getDBConnection();
    $customer_id = $_SESSION['customer_id'];
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");
    closeDBConnection($conn);
}

session_destroy();
header("Location: index.php");
exit();

