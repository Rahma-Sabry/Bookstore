<?php
/**
 * Search Page
 * Allows users to search for books
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$conn = getDBConnection();

$search_term = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
$books = [];

if ($search_term) {
    $books = searchBooks($conn, $search_term);
}

$page_title = "Search Books";
include 'includes/header.php';
?>

<div class="container">
    <div class="search-page">
        <h1>Search Books</h1>
        
        <form method="GET" action="search.php" class="search-form">
            <div class="form-group">
                <input type="text" name="q" id="search-input" placeholder="Search by title, ISBN, author, publisher, or description..." value="<?php echo htmlspecialchars($search_term); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <?php if ($search_term): ?>
            <div class="search-results">
                <h2>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</h2>
                <?php if (empty($books)): ?>
                    <p>No books found matching your search.</p>
                <?php else: ?>
                    <p>Found <?php echo count($books); ?> result(s)</p>
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
                                    <?php if ($book['isbn']): ?>
                                        <p class="isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                                    <?php endif; ?>
                                    <p class="publisher">Publisher: <?php echo htmlspecialchars($book['publisher_name'] ?? 'N/A'); ?></p>
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
        <?php endif; ?>
    </div>
</div>

<style>
.search-form {
    max-width: 600px;
    margin: 2rem auto;
    display: flex;
    gap: 1rem;
}

.search-form input {
    flex: 1;
    padding: 0.75rem;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.search-results {
    margin-top: 2rem;
}
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

