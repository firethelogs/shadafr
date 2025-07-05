<?php
require_once 'includes/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// Sanitize and validate input
$product_id      = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$customer_name   = htmlspecialchars(trim($_POST['customer_name']));
$phone           = htmlspecialchars(trim($_POST['phone']));
$address         = htmlspecialchars(trim($_POST['address']));
$taille          = htmlspecialchars(trim($_POST['taille']));
$color           = htmlspecialchars(trim($_POST['color']));
$delivery_choice = htmlspecialchars(trim($_POST['delivery_choice']));
$wilaya          = htmlspecialchars(trim($_POST['wilaya']));

// Validate required fields
if (!$product_id || empty($customer_name) || empty($phone) || empty($address) ||
    empty($taille) || empty($color) || empty($delivery_choice) || empty($wilaya)) {
    die("All fields are required. Please go back and fill in all fields.");
}

// Verify product exists
$stmt = $connection->prepare("SELECT id FROM products WHERE id = :id");
$stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$result = $stmt->execute();

if (!$result->fetchArray(SQLITE3_ASSOC)) {
    die("Invalid product selected.");
}

try {
    // Check delivery choice is valid
    if (!in_array($delivery_choice, ['domicile', 'bureau_noest'])) {
        die("Invalid delivery choice.");
    }

    // Insert order
    $stmt = $connection->prepare("
        INSERT INTO orders 
        (customer_name, phone, address, product_id, taille, color, delivery_choice, wilaya, status)
        VALUES 
        (:customer_name, :phone, :address, :product_id, :taille, :color, :delivery_choice, :wilaya, 'pending')
    ");

    $stmt->bindValue(':customer_name', $customer_name, SQLITE3_TEXT);
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':address', $address, SQLITE3_TEXT);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $stmt->bindValue(':taille', $taille, SQLITE3_TEXT);
    $stmt->bindValue(':color', $color, SQLITE3_TEXT);
    $stmt->bindValue(':delivery_choice', $delivery_choice, SQLITE3_TEXT);
    $stmt->bindValue(':wilaya', $wilaya, SQLITE3_TEXT);

    if ($stmt->execute()) {
        header("Location: product.php?id=" . $product_id . "&order=success");
        exit;
    } else {
        throw new Exception("Database error.");
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Error processing order: " . $e->getMessage() . " - " . $connection->lastErrorMsg());
}
