<?php
// Database configuration using SQLite
define('DB_FILE', __DIR__ . '/../database/shada.db');

// Create database directory if it doesn't exist
if (!file_exists(__DIR__ . '/../database')) {
    mkdir(__DIR__ . '/../database', 0777, true);
}

try {
    $connection = new SQLite3(DB_FILE);
    $connection->enableExceptions(true);
} catch (Exception $e) {
    die("Database Connection failed: " . $e->getMessage());
}

// Configure session
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Function to redirect if not logged in
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
