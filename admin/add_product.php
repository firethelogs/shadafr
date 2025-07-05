<?php
require_once '../includes/config.php';

// Require admin login
requireAdmin();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $tailles = trim($_POST['tailles'] ?? '');

    // Validate required fields
    if (empty($name) || $price === false || empty($tailles)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle main image upload
        $main_image = $_FILES['main_image'] ?? null;
        $upload_dir = '../uploads/products/';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!$main_image || $main_image['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a main image.';
        } else {
            // Generate unique filename
            $main_image_name = uniqid() . '_' . basename($main_image['name']);
            $main_image_path = $upload_dir . $main_image_name;

            try {
                // Begin transaction
                $connection->exec('BEGIN');

                // Move uploaded main image
                if (!move_uploaded_file($main_image['tmp_name'], $main_image_path)) {
                    throw new Exception('Failed to upload main image.');
                }

                // Insert product
                $stmt = $connection->prepare("
                    INSERT INTO products (name, description, price_dzd, tailles, main_image) 
                    VALUES (:name, :description, :price, :tailles, :main_image)
                ");
                
                $stmt->bindValue(':name', $name, SQLITE3_TEXT);
                $stmt->bindValue(':description', $description, SQLITE3_TEXT);
                $stmt->bindValue(':price', $price, SQLITE3_FLOAT);
                $stmt->bindValue(':tailles', $tailles, SQLITE3_TEXT);
                $stmt->bindValue(':main_image', $main_image_name, SQLITE3_TEXT);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to save product details.');
                }

                $product_id = $connection->lastInsertRowID();

                // Handle additional images
                if (!empty($_FILES['gallery_images']['name'][0])) {
                    foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['gallery_images']['error'][$key] === UPLOAD_ERR_OK) {
                            $gallery_image_name = uniqid() . '_' . basename($_FILES['gallery_images']['name'][$key]);
                            $gallery_image_path = $upload_dir . $gallery_image_name;

                            if (!move_uploaded_file($tmp_name, $gallery_image_path)) {
                                throw new Exception('Failed to upload gallery image.');
                            }

                            $stmt = $connection->prepare("
                                INSERT INTO product_images (product_id, image_path) 
                                VALUES (:product_id, :image_path)
                            ");
                            
                            $stmt->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
                            $stmt->bindValue(':image_path', $gallery_image_name, SQLITE3_TEXT);
                            
                            if (!$stmt->execute()) {
                                throw new Exception('Failed to save gallery image details.');
                            }
                        }
                    }
                }

                // Commit transaction
                $connection->exec('COMMIT');
                $success = 'Product added successfully!';

                // Clear form data
                $name = $price = $tailles = '';
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $connection->exec('ROLLBACK');
                $error = $e->getMessage();
                
                // Clean up uploaded files if they exist
                if (file_exists($main_image_path)) {
                    unlink($main_image_path);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Shada Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Add New Product</h1>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="container">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                <li><a href="view_products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo h($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>

        <div class="admin-form-container">
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="form-group">
                    <label for="name">Product Name:</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo h($name ?? ''); ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="description">Product Description:</label>
                    <textarea id="description" 
                              name="description" 
                              class="form-control" 
                              rows="4"><?php echo h($description ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price (DZD):</label>
                    <input type="number" 
                           id="price" 
                           name="price" 
                           class="form-control" 
                           value="<?php echo h($price ?? ''); ?>" 
                           step="0.01" 
                           min="0" 
                           required>
                </div>

                <div class="form-group">
                    <label for="tailles">Sizes (comma-separated):</label>
                    <input type="text" 
                           id="tailles" 
                           name="tailles" 
                           class="form-control" 
                           value="<?php echo h($tailles ?? ''); ?>" 
                           placeholder="S, M, L, XL" 
                           required>
                </div>

                <div class="form-group">
                    <label for="main_image">Main Image:</label>
                    <input type="file" 
                           id="main_image" 
                           name="main_image" 
                           class="form-control" 
                           accept="image/*" 
                           required>
                </div>

                <div class="form-group">
                    <label for="gallery_images">Additional Images:</label>
                    <input type="file" 
                           id="gallery_images" 
                           name="gallery_images[]" 
                           class="form-control" 
                           accept="image/*" 
                           multiple>
                    <small>Hold Ctrl/Cmd to select multiple images</small>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </form>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada Admin Panel</p>
        </div>
    </footer>
</body>
</html>
