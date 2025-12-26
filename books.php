<?php
/**
 * Books Listing Page
 * Displays all books or books by category
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$conn = getDBConnection();

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

if ($category_id > 0) {
    $books = getBooksByCategory($conn, $category_id, $limit, $offset);
    $category = $conn->query("SELECT * FROM categories WHERE category_id = $category_id")->fetch_assoc();
    $page_title = $category ? $category['category_name'] . " Books" : "Books";
    } else {
        $sql = "SELECT b.*, c.category_name, p.publisher_name,
                GROUP_CONCAT(DISTINCT a.author_name SEPARATOR ', ') as authors
                FROM books b
                JOIN categories c ON b.category_id = c.category_id
                JOIN publishers p ON b.publisher_id = p.publisher_id
                LEFT JOIN book_authors ba ON b.book_id = ba.book_id
                LEFT JOIN authors a ON ba.author_id = a.author_id
                GROUP BY b.book_id
                ORDER BY b.title
                LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        $books = $result->fetch_all(MYSQLI_ASSOC);
        $page_title = "All Books";
    }

$categories = getCategories($conn);

include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($category_id > 0 && $category): ?>
            <p><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
        <?php endif; ?>
    </div>

    <div class="books-listing">
        <?php if (empty($books)): ?>
            <p>No books found.</p>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-image">
                            <?php if ($book['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h3><a href="book-details.php?id=<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['title']); ?></a></h3>
                            <p class="author">By <?php echo htmlspecialchars($book['authors'] ?? 'N/A'); ?></p>
                            <p class="category"><?php echo htmlspecialchars($book['category_name']); ?></p>
                            <p class="price">$<?php echo number_format($book['price'], 2); ?></p>
                            <div class="book-actions">
                                <a href="book-details.php?id=<?php echo $book['book_id']; ?>" class="btn btn-primary">View Details</a>
                                <?php if ($book['stock_quantity'] > 0): ?>
                                    <a href="cart.php?action=add&book_id=<?php echo $book['book_id']; ?>" class="btn btn-success">Add to Cart</a>
                                <?php else: ?>
                                    <span class="btn btn-disabled">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

