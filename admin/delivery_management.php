<?php
require_once '../includes/config.php';
require_once '../includes/noest_config.php';

// Require admin login
requireAdmin();

$success = $error = '';

// Handle order tracking update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    
    if ($orderId) {
        try {
            // Get order details
            $stmt = $connection->prepare("SELECT noest_tracking_number FROM orders WHERE id = :id");
            $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
            $result = $stmt->execute();
            $order = $result->fetchArray(SQLITE3_ASSOC);
            
            if ($order && !empty($order['noest_tracking_number'])) {
                // Get tracking info from Noest API
                $trackingInfo = $noestAPI->getTracking($order['noest_tracking_number']);
                
                if ($trackingInfo) {
                    // Update delivery status
                    $stmt = $connection->prepare("
                        UPDATE orders SET 
                            delivery_status = :status,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :id
                    ");
                    $stmt->bindValue(':status', $trackingInfo['status'] ?? 'unknown', SQLITE3_TEXT);
                    $stmt->bindValue(':id', $orderId, SQLITE3_INTEGER);
                    $stmt->execute();
                    
                    $success = "Tracking information updated successfully.";
                }
            }
        } catch (Exception $e) {
            $error = "Failed to update tracking: " . $e->getMessage();
        }
    }
}

// Get all orders with delivery information
$query = "
    SELECT 
        o.*,
        p.name as product_name,
        p.price_dzd
    FROM orders o
    JOIN products p ON o.product_id = p.id
    ORDER BY o.id DESC
";
$orders = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Livraisons - Shada Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .delivery-status {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            display: inline-block;
        }
        
        .status-pending { background: #ffeaa7; color: #2d3436; }
        .status-created { background: #74b9ff; color: white; }
        .status-picked-up { background: #fdcb6e; color: white; }
        .status-in-transit { background: #e17055; color: white; }
        .status-delivered { background: #00b894; color: white; }
        .status-failed { background: #d63031; color: white; }
        .status-error { background: #d63031; color: white; }
        .status-api-failed { background: #636e72; color: white; }
        
        .tracking-code {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            border: 1px solid #dee2e6;
        }
        
        .delivery-type {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
        }
        
        .delivery-type i {
            color: #6c757d;
        }
        
        .customer-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .customer-name {
            font-weight: 600;
            color: #495057;
        }
        
        .customer-phone {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            min-width: 0; /* Allow shrinking */
        }
        
        .product-name {
            font-weight: 500;
            color: #495057;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .product-details {
            font-size: 0.875rem;
            color: #6c757d;
            display: flex;
            gap: 0.5rem;
            margin-top: 0.25rem;
            flex-wrap: wrap; /* Allow wrapping if needed */
            align-items: center;
        }
        
        .product-detail-item {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.125rem 0.5rem;
            background: #f8f9fa;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap; /* Prevent breaking within badge */
            flex-shrink: 0; /* Prevent badges from shrinking */
        }
        
        .product-detail-item i {
            font-size: 0.625rem;
            opacity: 0.7;
        }
        
        .delivery-cost {
            font-weight: 600;
            color: #28a745;
        }
        
        .delivery-cost.zero {
            color: #6c757d;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #fff;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #495057;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        /* Table column widths */
        .admin-table th:nth-child(1) { width: 5%; }   /* ID */
        .admin-table th:nth-child(2) { width: 20%; }  /* Client */
        .admin-table th:nth-child(3) { width: 25%; }  /* Produit */
        .admin-table th:nth-child(4) { width: 12%; }  /* Wilaya */
        .admin-table th:nth-child(5) { width: 10%; }  /* Livraison */
        .admin-table th:nth-child(6) { width: 10%; }  /* Statut */
        .admin-table th:nth-child(7) { width: 10%; }  /* Suivi */
        .admin-table th:nth-child(8) { width: 8%; }   /* Coût */
        
        .admin-table td {
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 0; /* Force table-layout to respect width */
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
                <li><a href="delivery_management.php" class="active"><i class="fas fa-truck"></i> Deliveries</a></li>
                <li><a href="noest_sync.php"><i class="fas fa-sync"></i> Noest Sync</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="page-header">
            <h1>Gestion des Livraisons</h1>
            <p>Gérez les livraisons et le suivi des commandes</p>
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

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $connection->querySingle("SELECT COUNT(*) FROM orders"); ?></div>
                <div class="stat-label">Total Commandes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $connection->querySingle("SELECT COUNT(*) FROM orders WHERE delivery_status = 'pending'"); ?></div>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $connection->querySingle("SELECT COUNT(*) FROM orders WHERE delivery_status = 'created'"); ?></div>
                <div class="stat-label">Créées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $connection->querySingle("SELECT COUNT(*) FROM orders WHERE delivery_status = 'delivered'"); ?></div>
                <div class="stat-label">Livrées</div>
            </div>
        </div>

        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Produit</th>
                        <th>Wilaya</th>
                        <th>Livraison</th>
                        <th>Statut</th>
                        <th>Suivi</th>
                        <th>Coût</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetchArray(SQLITE3_ASSOC)): ?>
                        <tr>
                            <td><?php echo $order['id']; ?></td>
                            <td>
                                <div class="customer-info">
                                    <div class="customer-name"><?php echo h($order['customer_name']); ?></div>
                                    <div class="customer-phone"><?php echo h($order['phone']); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="product-info">
                                    <div class="product-name"><?php echo h($order['product_name']); ?></div>
                                    <div class="product-details">
                                        <?php if (!empty($order['taille'])): ?>
                                            <span class="product-detail-item">
                                                <i class="fas fa-ruler"></i>
                                                <?php echo h($order['taille']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['color'])): ?>
                                            <span class="product-detail-item">
                                                <i class="fas fa-palette"></i>
                                                <?php echo h($order['color']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo h($order['wilaya']); ?></td>
                            <td>
                                <div class="delivery-type">
                                    <?php if ($order['delivery_choice'] === 'domicile'): ?>
                                        <i class="fas fa-home"></i> Domicile
                                    <?php else: ?>
                                        <i class="fas fa-building"></i> Bureau
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="delivery-status status-<?php echo $order['delivery_status']; ?>">
                                    <?php echo h($order['delivery_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($order['noest_tracking_number']): ?>
                                    <span class="tracking-code"><?php echo h($order['noest_tracking_number']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($order['delivery_cost'] > 0): ?>
                                    <span class="delivery-cost"><?php echo number_format($order['delivery_cost'], 0); ?> DZD</span>
                                <?php else: ?>
                                    <span class="delivery-cost zero">0 DZD</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($order['noest_tracking_number']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" name="update_tracking" class="btn-small btn-track">
                                                <i class="fas fa-sync"></i> Mettre à jour
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Pas de suivi</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
