<?php
/**
 * Admin Panel - Test All Pages
 * This page helps verify all admin pages are accessible
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

session_start();

if (!isAdmin()) {
    redirect('login.php');
}

$page_title = "Test All Admin Pages";
include '../includes/header.php';
?>

<div class="container">
    <h1>Admin Panel - Page Test</h1>
    <p>Use this page to test all admin panel pages and verify they're working correctly.</p>
    
    <div class="test-results">
        <h2>Admin Pages Checklist</h2>
        
        <div class="test-section">
            <h3>Core Pages</h3>
            <ul class="test-list">
                <li>
                    <a href="login.php" target="_blank">Admin Login</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="index.php" target="_blank">Admin Dashboard</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="books.php" target="_blank">Manage Books (List)</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="books.php?action=add" target="_blank">Add New Book</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="orders.php" target="_blank">Customer Orders</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="publisher-orders.php" target="_blank">Publisher Orders</a>
                    <span class="test-status">✓</span>
                </li>
                <li>
                    <a href="reports.php" target="_blank">System Reports</a>
                    <span class="test-status">✓</span>
                </li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3>Test Features</h3>
            <ul class="test-list">
                <li>
                    <strong>CSS Loading:</strong> Check if pages have proper styling
                    <span class="test-status">Check each page</span>
                </li>
                <li>
                    <strong>Navigation:</strong> Verify header navigation works
                    <span class="test-status">Check header links</span>
                </li>
                <li>
                    <strong>Forms:</strong> Test all forms submit correctly
                    <span class="test-status">Test each form</span>
                </li>
                <li>
                    <strong>Tables:</strong> Verify data displays in tables
                    <span class="test-status">Check table styling</span>
                </li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3>Quick Actions</h3>
            <div class="quick-actions">
                <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
                <a href="books.php?action=add" class="btn btn-success">Add Book</a>
                <a href="reports.php" class="btn btn-info">View Reports</a>
            </div>
        </div>
    </div>
</div>

<style>
.test-results {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin-top: 2rem;
}

.test-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.test-section:last-child {
    border-bottom: none;
}

.test-list {
    list-style: none;
    padding: 0;
}

.test-list li {
    padding: 0.75rem;
    margin: 0.5rem 0;
    background: #f8f9fa;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.test-list a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.test-list a:hover {
    text-decoration: underline;
}

.test-status {
    color: #27ae60;
    font-weight: bold;
}

.quick-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    transition: opacity 0.3s;
}

.btn:hover {
    opacity: 0.9;
}

.btn-primary {
    background-color: #667eea;
    color: white;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-info {
    background-color: #3498db;
    color: white;
}
</style>

<?php
include '../includes/footer.php';
?>

