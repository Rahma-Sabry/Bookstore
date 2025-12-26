<?php
/**
 * Admin - Manage Books
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$conn = getDBConnection();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $isbn = sanitizeInput($_POST['isbn'] ?? '');
        $title = sanitizeInput($_POST['title'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $publisher_id = (int)($_POST['publisher_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $threshold = (int)($_POST['threshold'] ?? 10);
        $description = sanitizeInput($_POST['description'] ?? '');
        $publication_year = (int)($_POST['publication_year'] ?? date('Y'));
        $author_ids = $_POST['author_ids'] ?? [];
        
        if ($title && $category_id && $publisher_id && $price > 0) {
            $stmt = $conn->prepare("INSERT INTO books (isbn, title, category_id, publisher_id, price, stock_quantity, threshold, description, publication_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiidissi", $isbn, $title, $category_id, $publisher_id, $price, $stock_quantity, $threshold, $description, $publication_year);
            
            if ($stmt->execute()) {
                $book_id = $conn->insert_id;
                
                // Add authors
                foreach ($author_ids as $author_id) {
                    $author_id = (int)$author_id;
                    if ($author_id > 0) {
                        $stmt2 = $conn->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
                        $stmt2->bind_param("ii", $book_id, $author_id);
                        $stmt2->execute();
                    }
                }
                
                $message = 'Book added successfully!';
            } else {
                $error = 'Failed to add book.';
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    } elseif ($action == 'update') {
        $book_id = (int)($_POST['book_id'] ?? 0);
        $title = sanitizeInput($_POST['title'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $publisher_id = (int)($_POST['publisher_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $threshold = (int)($_POST['threshold'] ?? 10);
        $description = sanitizeInput($_POST['description'] ?? '');
        $publication_year = (int)($_POST['publication_year'] ?? date('Y'));
        $author_ids = $_POST['author_ids'] ?? [];
        
        if ($book_id > 0 && $title && $category_id && $publisher_id && $price > 0) {
            $stmt = $conn->prepare("UPDATE books SET title = ?, category_id = ?, publisher_id = ?, price = ?, stock_quantity = ?, threshold = ?, description = ?, publication_year = ? WHERE book_id = ?");
            $stmt->bind_param("siidissii", $title, $category_id, $publisher_id, $price, $stock_quantity, $threshold, $description, $publication_year, $book_id);
            
            if ($stmt->execute()) {
                // Update authors
                // First, remove all existing author associations
                $conn->query("DELETE FROM book_authors WHERE book_id = $book_id");
                
                // Then add new author associations
                foreach ($author_ids as $author_id) {
                    $author_id = (int)$author_id;
                    if ($author_id > 0) {
                        $stmt2 = $conn->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
                        $stmt2->bind_param("ii", $book_id, $author_id);
                        $stmt2->execute();
                    }
                }
                
                $message = 'Book updated successfully!';
            } else {
                $error = 'Failed to update book.';
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    }
}

// Get action and book ID
$action = $_GET['action'] ?? 'list';
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get categories, publishers, and authors
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name")->fetch_all(MYSQLI_ASSOC);
$publishers = $conn->query("SELECT * FROM publishers ORDER BY publisher_name")->fetch_all(MYSQLI_ASSOC);
$authors = $conn->query("SELECT * FROM authors ORDER BY author_name")->fetch_all(MYSQLI_ASSOC);

$book = null;
$book_authors = [];
if ($book_id > 0) {
    $book = $conn->query("SELECT * FROM books WHERE book_id = $book_id")->fetch_assoc();
    if ($book) {
        $result = $conn->query("SELECT author_id FROM book_authors WHERE book_id = $book_id");
        $book_authors = array_column($result->fetch_all(MYSQLI_ASSOC), 'author_id');
    }
}

// Get all books for listing
$books = [];
if ($action == 'list') {
    $sql = "SELECT b.*, c.category_name, p.publisher_name,
            GROUP_CONCAT(a.author_name SEPARATOR ', ') as authors
            FROM books b
            JOIN categories c ON b.category_id = c.category_id
            JOIN publishers p ON b.publisher_id = p.publisher_id
            LEFT JOIN book_authors ba ON b.book_id = ba.book_id
            LEFT JOIN authors a ON ba.author_id = a.author_id
            GROUP BY b.book_id
            ORDER BY b.title";
    $books = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

$page_title = "Manage Books";
include '../includes/header.php';
?>

<div class="container">
    <h1>Manage Books</h1>
    
    <?php if ($message): ?>
        <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($action == 'add' || $action == 'edit'): ?>
        <h2><?php echo $action == 'add' ? 'Add New Book' : 'Edit Book'; ?></h2>
        <form method="POST" action="books.php">
            <input type="hidden" name="action" value="<?php echo $action == 'add' ? 'add' : 'update'; ?>">
            <?php if ($action == 'edit'): ?>
                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($book['category_id']) && $book['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="publisher_id">Publisher *</label>
                <select id="publisher_id" name="publisher_id" required>
                    <option value="">Select Publisher</option>
                    <?php foreach ($publishers as $pub): ?>
                        <option value="<?php echo $pub['publisher_id']; ?>" <?php echo (isset($book['publisher_id']) && $book['publisher_id'] == $pub['publisher_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pub['publisher_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="author_ids">Authors *</label>
                <select id="author_ids" name="author_ids[]" multiple required>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?php echo $author['author_id']; ?>" <?php echo in_array($author['author_id'], $book_authors) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($author['author_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Hold Ctrl/Cmd to select multiple authors</small>
            </div>
            
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $book['price'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity *</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?php echo $book['stock_quantity'] ?? 0; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="threshold">Threshold (Minimum Stock) *</label>
                <input type="number" id="threshold" name="threshold" min="0" value="<?php echo $book['threshold'] ?? 10; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="publication_year">Publication Year</label>
                <input type="number" id="publication_year" name="publication_year" min="1000" max="9999" value="<?php echo $book['publication_year'] ?? date('Y'); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($book['description'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary"><?php echo $action == 'add' ? 'Add Book' : 'Update Book'; ?></button>
            <a href="books.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php else: ?>
        <div style="margin-bottom: 1rem;">
            <a href="books.php?action=add" class="btn btn-primary">Add New Book</a>
        </div>
        
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Authors</th>
                    <th>Category</th>
                    <th>Publisher</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Threshold</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="9">No books found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['isbn'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($b['title']); ?></td>
                            <td><?php echo htmlspecialchars($b['authors'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($b['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($b['publisher_name']); ?></td>
                            <td>$<?php echo number_format($b['price'], 2); ?></td>
                            <td><?php echo $b['stock_quantity']; ?></td>
                            <td><?php echo $b['threshold']; ?></td>
                            <td>
                                <a href="books.php?action=edit&id=<?php echo $b['book_id']; ?>" class="btn btn-small">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.admin-table {
    width: 100%;
    background: white;
    border-collapse: collapse;
    margin-top: 1rem;
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

.btn-small {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.success-message {
    background-color: #27ae60;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.error-message {
    background-color: #e74c3c;
    color: white;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}
</style>

<?php
closeDBConnection($conn);
include '../includes/footer.php';
?>

