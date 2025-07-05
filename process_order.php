<?php
require_once 'includes/config.php';
require_once 'includes/noest_config.php';
require_once 'includes/delivery_fees.php';

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
$wilaya_id       = filter_input(INPUT_POST, 'wilaya', FILTER_VALIDATE_INT);
$commune         = htmlspecialchars(trim($_POST['commune'] ?? ''));

// Validate required fields
if (!$product_id || empty($customer_name) || empty($phone) || empty($address) ||
    empty($taille) || empty($color) || empty($delivery_choice) || !$wilaya_id) {
    die("All fields are required. Please go back and fill in all fields.");
}

// Get wilaya name from ID
$wilaya_name = DeliveryFees::getWilayaNameById($wilaya_id);

// Calculate delivery fee
$delivery_type = ($delivery_choice === 'domicile') ? 'domicile' : 'stopdesk';
$delivery_fee = DeliveryFees::getFeeByWilayaId($wilaya_id, $delivery_type);

// Verify product exists and get product details
$stmt = $connection->prepare("SELECT id, name, price_dzd, noest_product_ref FROM products WHERE id = :id");
$stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$product = $result->fetchArray(SQLITE3_ASSOC);

if (!$product) {
    die("Invalid product selected.");
}

// Calculate total price
$total_price = $product['price_dzd'] + $delivery_fee;

try {
    // Check delivery choice is valid
    if (!in_array($delivery_choice, ['domicile', 'bureau_noest'])) {
        die("Invalid delivery choice.");
    }

    // Start transaction
    $connection->exec('BEGIN');
    
    // Insert order into database first
    $stmt = $connection->prepare("
        INSERT INTO orders 
        (customer_name, phone, address, product_id, taille, color, delivery_choice, wilaya, status, delivery_status, delivery_cost)
        VALUES 
        (:customer_name, :phone, :address, :product_id, :taille, :color, :delivery_choice, :wilaya, 'pending', 'pending', :delivery_cost)
    ");
    
    $stmt->bindValue(':customer_name', $customer_name, SQLITE3_TEXT);
    $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
    $stmt->bindValue(':address', $address, SQLITE3_TEXT);
    $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $stmt->bindValue(':taille', $taille, SQLITE3_TEXT);
    $stmt->bindValue(':color', $color, SQLITE3_TEXT);
    $stmt->bindValue(':delivery_choice', $delivery_choice, SQLITE3_TEXT);
    $stmt->bindValue(':wilaya', $wilaya_name, SQLITE3_TEXT);
    $stmt->bindValue(':delivery_cost', $delivery_fee, SQLITE3_FLOAT);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert order into database.");
    }
    
    $orderId = $connection->lastInsertRowID();
    
    // Only create Noest delivery order if delivery choice is 'domicile'
    if ($delivery_choice === 'domicile') {
        try {
            // Generate product reference for tracking purposes (optional)
            $productRef = $product['noest_product_ref'];
            if (empty($productRef)) {
                // Generate a product reference for internal tracking
                $productRef = 'SHADA-' . $product['id'] . '-' . strtoupper(substr(md5($product['name']), 0, 6));
                
                // Update product with the reference for future use
                $stmt = $connection->prepare("UPDATE products SET noest_product_ref = :ref WHERE id = :id");
                $stmt->bindValue(':ref', $productRef, SQLITE3_TEXT);
                $stmt->bindValue(':id', $product['id'], SQLITE3_INTEGER);
                $stmt->execute();
            }

            // Map wilaya to default commune (you can expand this mapping)
            $communeMapping = [
                16 => 'Alger Centre',   // Alger
                31 => 'Oran',          // Oran  
                25 => 'Constantine',   // Constantine
                9 => 'Blida',         // Blida
                1 => 'Adrar',         // Adrar
                2 => 'Chlef',         // Chlef
                // Add more mappings as needed
            ];
            
            $commune = $communeMapping[$wilaya_id] ?? $wilaya_name;
            
            // Prepare order data for Noest API without stock management
            $orderData = [
                'reference' => 'SHADA-' . $orderId,
                'client' => $customer_name,
                'phone' => $phone,
                'adresse' => $address,
                'wilaya_id' => $wilaya_id,
                'commune' => $commune,
                'montant' => $total_price, // Use total price including delivery fee
                'remarque' => "Produit: {$product['name']}, Taille: {$taille}, Couleur: {$color}",
                'produit' => $product['name'], // Use product name instead of reference
                'type_id' => 1, // 1 = Livraison
                'poids' => 1, // Default weight 1kg
                'stop_desk' => 0, // 0 = domicile
                'stock' => 0, // 0 = No stock management
                'quantite' => '1', // Quantity of the product
                'can_open' => 1 // Customer can open package
            ];

            // Create delivery order with Noest
            $noestResponse = $noestAPI->createDeliveryOrder($orderData);

            if ($noestResponse && isset($noestResponse['success']) && $noestResponse['success']) {
                // Update order with Noest details
                $stmt = $connection->prepare("
                    UPDATE orders SET 
                        noest_tracking_number = :tracking_number,
                        delivery_status = 'created',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = :order_id
                ");
                
                $stmt->bindValue(':tracking_number', $noestResponse['tracking'] ?? '', SQLITE3_TEXT);
                $stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update order with Noest details.");
                }
                
                // Log successful integration
                error_log("Noest delivery order created successfully: Order ID {$orderId}, Tracking: {$noestResponse['tracking']}");
            } else {
                throw new Exception("Noest API did not return success response");
            }
            
        } catch (Exception $e) {
            // Log error but don't fail the order
            error_log("Noest API error for order {$orderId}: " . $e->getMessage());
            
            // Update order status to indicate delivery error
            $stmt = $connection->prepare("UPDATE orders SET delivery_status = 'error' WHERE id = :order_id");
            $stmt->bindValue(':order_id', $orderId, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }
    
    // Commit transaction
    $connection->exec('COMMIT');
    
    // Redirect to thank you page
    header("Location: merci.php?order_id=" . $orderId);
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    $connection->exec('ROLLBACK');
    error_log("Order processing failed: " . $e->getMessage());
    die("Error processing order: " . $e->getMessage());
}
