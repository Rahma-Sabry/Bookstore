<?php
/**
 * Bookstore Home Page
 * Main landing page displaying featured books
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$conn = getDBConnection();

// Get featured books (latest 6 books)
$featured_books = getFeaturedBooks($conn, 6);

// Get categories for navigation
$categories = getCategories($conn);

$page_title = "Welcome to Our Bookstore";
include 'includes/header.php';
?>

<div class="container">
    <div class="hero-section">
        <h1>Welcome to Our Bookstore</h1>
        <p>Discover thousands of books across various genres</p>
    </div>

    <div class="categories-section">
        <h2>Browse by Category</h2>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="books.php?category=<?php echo $category['category_id']; ?>" class="category-card">
                    <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="featured-books-section">
        <h2>Featured Books</h2>
        <div class="books-grid">
            <?php if (empty($featured_books)): ?>
                <p>No books available at the moment.</p>
            <?php else: ?>
                <?php foreach ($featured_books as $book): ?>
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
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

