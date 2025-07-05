<?php
require_once '../includes/config.php';
require_once '../includes/noest_config.php';

// Require admin login
requireAdmin();

$success = $error = '';

// Handle product sync to Noest
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sync_product'])) {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        
        if ($productId) {
            try {
                // Get product details
                $stmt = $connection->prepare("SELECT * FROM products WHERE id = :id");
                $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
                $result = $stmt->execute();
                $product = $result->fetchArray(SQLITE3_ASSOC);
                
                if ($product) {
                    // Generate product reference if not exists
                    $productRef = $product['noest_product_ref'];
                    if (empty($productRef)) {
                        $productRef = 'SHADA-' . $product['id'] . '-' . strtoupper(substr(md5($product['name']), 0, 6));
                        
                        // Update product with the reference
                        $stmt = $connection->prepare("UPDATE products SET noest_product_ref = :ref WHERE id = :id");
                        $stmt->bindValue(':ref', $productRef, SQLITE3_TEXT);
                        $stmt->bindValue(':id', $productId, SQLITE3_INTEGER);
                        $stmt->execute();
                    }
                    
                    // Prepare product data for Noest
                    $productData = [
                        'reference' => $productRef,
                        'name' => $product['name'],
                        'description' => $product['description'] ?? '',
                        'price' => $product['price_dzd'],
                        'quantity' => 100, // Default stock quantity
                        'weight' => 1 // Default weight
                    ];
                    
                    // Note: Stock management is not available on this Noest account
                    // Instead, we'll just generate the product reference for tracking purposes
                    $success = "Référence produit générée: '{$product['name']}' (Ref: {$productRef}). Note: La gestion de stock Noest n'est pas disponible sur ce compte.";
                }
            } catch (Exception $e) {
                $error = "Erreur lors de la synchronisation: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['sync_all_products'])) {
        try {
            $syncCount = 0;
            $errorCount = 0;
            
            // Get all products
            $stmt = $connection->prepare("SELECT * FROM products");
            $result = $stmt->execute();
            
            while ($product = $result->fetchArray(SQLITE3_ASSOC)) {
                try {
                    // Generate product reference if not exists
                    $productRef = $product['noest_product_ref'];
                    if (empty($productRef)) {
                        $productRef = 'SHADA-' . $product['id'] . '-' . strtoupper(substr(md5($product['name']), 0, 6));
                        
                        // Update product with the reference
                        $stmt2 = $connection->prepare("UPDATE products SET noest_product_ref = :ref WHERE id = :id");
                        $stmt2->bindValue(':ref', $productRef, SQLITE3_TEXT);
                        $stmt2->bindValue(':id', $product['id'], SQLITE3_INTEGER);
                        $stmt2->execute();
                    }
                    
                    // Prepare product data for Noest
                    $productData = [
                        'reference' => $productRef,
                        'name' => $product['name'],
                        'description' => $product['description'] ?? '',
                        'price' => $product['price_dzd'],
                        'quantity' => 100,
                        'weight' => 1
                    ];
                    
                    // Note: Stock management is not available on this Noest account
                    // Instead, we'll just generate the product reference for tracking purposes
                    $syncCount++;
                    
                    // Small delay to avoid overwhelming the system
                    usleep(100000); // 0.1 seconds
                    
                } catch (Exception $e) {
                    $errorCount++;
                }
            }
            
            $success = "Génération terminée: {$syncCount} références générées, {$errorCount} erreurs";
            
        } catch (Exception $e) {
            $error = "Erreur lors de la synchronisation globale: " . $e->getMessage();
        }
    }
}

// Get all products with their Noest references
$query = "SELECT * FROM products ORDER BY id DESC";
$products = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synchronisation Noest - Shada Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .sync-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-synced { background: #d4edda; color: #155724; }
        .status-not-synced { background: #f8d7da; color: #721c24; }
        
        .product-ref {
            font-family: monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .sync-actions {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .btn-sync {
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-sync:hover {
            background: #218838;
        }
        
        .btn-sync-single {
            background: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .btn-sync-single:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <div class="container">
            <div class="nav-brand">
                <h2>Shada Admin</h2>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="add_product.php"><i class="fas fa-plus"></i> Add Product</a></li>
                <li><a href="view_products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="view_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="delivery_management.php"><i class="fas fa-truck"></i> Deliveries</a></li>
                <li><a href="noest_sync.php" class="active"><i class="fas fa-sync"></i> Noest Sync</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Synchronisation Noest</h1>
            <p>Gérez les produits dans votre stock Noest</p>
        </div>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Information:</strong> Le module de stockage Noest n'est pas disponible sur votre compte. 
            Cette fonction génère uniquement des références de produits pour le suivi des commandes. 
            Les commandes sont créées directement sans gestion de stock automatique.
        </div>

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

        <div class="sync-actions">
            <form method="POST" style="display: inline;">
                <button type="submit" name="sync_all_products" class="btn-sync" 
                        onclick="return confirm('Générer les références pour tous les produits ?')">
                    <i class="fas fa-sync"></i> Générer toutes les références
                </button>
            </form>
        </div>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Référence Noest</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <strong><?php echo h($product['name']); ?></strong>
                                <?php if (!empty($product['description'])): ?>
                                    <br><small><?php echo h(substr($product['description'], 0, 50)); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($product['price_dzd'], 2); ?> DZD</td>
                            <td>
                                <?php if ($product['noest_product_ref']): ?>
                                    <span class="product-ref"><?php echo h($product['noest_product_ref']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Non généré</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($product['noest_product_ref']): ?>
                                    <span class="sync-status status-synced">Référence générée</span>
                                <?php else: ?>
                                    <span class="sync-status status-not-synced">Pas de référence</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="sync_product" class="btn-sync-single">
                                        <i class="fas fa-sync"></i> Générer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="info-box" style="margin-top: 2rem; padding: 1rem; background: #e9ecef; border-radius: 4px;">
            <h3><i class="fas fa-info-circle"></i> Comment ça marche</h3>
            <ul>
                <li><strong>Références:</strong> Chaque produit reçoit une référence unique (ex: SHADA-1-ABC123)</li>
                <li><strong>Commandes:</strong> Les commandes utilisent ces références pour le suivi Noest</li>
                <li><strong>Livraison:</strong> Les frais de livraison sont calculés automatiquement selon les tarifs Noest</li>
                <li><strong>Suivi:</strong> Les numéros de suivi sont automatiquement générés lors de la création des commandes</li>
            </ul>
        </div>
    </main>
</body>
</html>
