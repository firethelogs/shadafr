<?php
require_once 'includes/config.php';

try {
    $connection->exec("
        ALTER TABLE orders 
        ADD COLUMN delivery_choice TEXT NOT NULL DEFAULT 'domicile' 
        CHECK(delivery_choice IN ('domicile', 'bureau_noest'))
    ");
    echo "Column 'delivery_choice' added successfully.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
