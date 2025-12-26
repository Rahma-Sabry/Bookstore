<?php
/**
 * Book Details Page
 * Displays detailed information about a specific book
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

session_start();
$conn = getDBConnection();

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    header("Location: books.php");
    exit();
}

$book = getBookById($conn, $book_id);

if (!$book) {
    header("Location: books.php");
    exit();
}

$reviews = getBookReviews($conn, $book_id);

$page_title = $book['title'];
include 'includes/header.php';
?>

<div class="container">
    <div class="book-details">
        <div class="book-detail-content">
            <div class="book-detail-image">
                <?php if ($book['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>">
                <?php else: ?>
                    <div class="placeholder-image large">No Image Available</div>
                <?php endif; ?>
            </div>
            <div class="book-detail-info">
                <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="author">By <strong><?php echo htmlspecialchars($book['authors'] ?? 'N/A'); ?></strong></p>
                <p class="category">Category: <?php echo htmlspecialchars($book['category_name']); ?></p>
                <?php if ($book['isbn']): ?>
                    <p class="isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                <?php endif; ?>
                <?php if ($book['publisher_name']): ?>
                    <p class="publisher">Publisher: <?php echo htmlspecialchars($book['publisher_name']); ?></p>
                <?php endif; ?>
                <?php if ($book['publication_year']): ?>
                    <p class="pub-date">Published: <?php echo $book['publication_year']; ?></p>
                <?php endif; ?>
                <p class="price large">$<?php echo number_format($book['price'], 2); ?></p>
                <p class="stock">Stock: <?php echo $book['stock_quantity']; ?> available</p>
                
                <?php if ($book['description']): ?>
                    <div class="description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <div class="book-actions">
                    <?php if ($book['stock_quantity'] > 0): ?>
                        <a href="cart.php?action=add&book_id=<?php echo $book['book_id']; ?>" class="btn btn-success btn-large">Add to Cart</a>
                    <?php else: ?>
                        <span class="btn btn-disabled btn-large">Out of Stock</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if ($book['author_bio']): ?>
            <div class="author-bio">
                <h2>About the Authors</h2>
                <p><?php echo nl2br(htmlspecialchars($book['author_bio'])); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="reviews-section">
            <h2>Reviews</h2>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet. Be the first to review this book!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                            <span class="rating"><?php echo str_repeat('★', $review['rating']); ?><?php echo str_repeat('☆', 5 - $review['rating']); ?></span>
                            <span class="review-date"><?php echo date('M d, Y', strtotime($review['review_date'])); ?></span>
                        </div>
                        <?php if ($review['review_text']): ?>
                            <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.book-details {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.book-detail-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.book-detail-image img {
    width: 100%;
    border-radius: 8px;
}

.placeholder-image.large {
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e0e0e0;
    border-radius: 8px;
}

.book-detail-info h1 {
    margin-bottom: 1rem;
    color: #2c3e50;
}

.price.large {
    font-size: 2rem;
    margin: 1.5rem 0;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.author-bio,
.reviews-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #ddd;
}

.review {
    background: #f9f9f9;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.review-header {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
    align-items: center;
}

.rating {
    color: #f39c12;
}

@media (max-width: 768px) {
    .book-detail-content {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
closeDBConnection($conn);
include 'includes/footer.php';
?>

