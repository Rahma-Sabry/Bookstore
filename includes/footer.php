    </main>
    <?php
    // Determine base path for assets (works from both root and admin folders)
    $base_path = '';
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $base_path = '../';
    }
    ?>
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>Your trusted online bookstore offering a wide selection of books across all genres.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_path; ?>books.php">Books</a></li>
                        <li><a href="<?php echo $base_path; ?>search.php">Search</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@bookstore.com</p>
                    <p>Phone: (123) 456-7890</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Bookstore. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="<?php echo $base_path; ?>assets/js/main.js"></script>
</body>
</html>

