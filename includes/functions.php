<?php
/**
 * Common Functions for Bookstore
 */

/**
 * Get featured books
 */
function getFeaturedBooks($conn, $limit = 6) {
    $sql = "SELECT b.*, c.category_name, p.publisher_name,
            GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
            FROM books b
            JOIN categories c ON b.category_id = c.category_id
            JOIN publishers p ON b.publisher_id = p.publisher_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            WHERE b.stock_quantity > 0
            GROUP BY b.book_id
            ORDER BY b.created_at DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get all categories
 */
function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY category_name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get books by category
 */
function getBooksByCategory($conn, $category_id, $limit = 20, $offset = 0) {
    $sql = "SELECT b.*, c.category_name, p.publisher_name,
            GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
            FROM books b
            JOIN categories c ON b.category_id = c.category_id
            JOIN publishers p ON b.publisher_id = p.publisher_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            WHERE b.category_id = ?
            GROUP BY b.book_id
            ORDER BY b.title
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $category_id, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get book by ID
 */
function getBookById($conn, $book_id) {
    $sql = "SELECT b.*, c.category_name, p.publisher_name, p.publisher_id,
            GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors,
            GROUP_CONCAT(DISTINCT a.bio SEPARATOR ' | ') as author_bio
            FROM books b
            JOIN categories c ON b.category_id = c.category_id
            JOIN publishers p ON b.publisher_id = p.publisher_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            WHERE b.book_id = ?
            GROUP BY b.book_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Search books
 */
function searchBooks($conn, $search_term, $limit = 20) {
    $search_term = "%{$search_term}%";
    $sql = "SELECT DISTINCT b.*, c.category_name, p.publisher_name,
            GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
            FROM books b
            JOIN categories c ON b.category_id = c.category_id
            JOIN publishers p ON b.publisher_id = p.publisher_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            WHERE b.title LIKE ? 
               OR b.isbn LIKE ?
               OR b.description LIKE ?
               OR a.author_name LIKE ?
               OR p.publisher_name LIKE ?
            GROUP BY b.book_id
            ORDER BY b.title
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $search_term, $search_term, $search_term, $search_term, $search_term, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get book reviews
 */
function getBookReviews($conn, $book_id) {
    $sql = "SELECT r.*, c.first_name, c.last_name 
            FROM reviews r
            JOIN customers c ON r.customer_id = c.customer_id
            WHERE r.book_id = ?
            ORDER BY r.review_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect function
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

?>

