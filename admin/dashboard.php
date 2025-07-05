<?php
require_once '../includes/config.php';

// Require admin login
requireAdmin();

// Get counts for dashboard
$counts = [
    'products' => $connection->querySingle("SELECT COUNT(*) FROM products"),
    'orders' => $connection->querySingle("SELECT COUNT(*) FROM orders"),
    'pending_orders' => $connection->querySingle("SELECT COUNT(*) FROM orders WHERE status = 'pending'")
];

// Get recent orders
$query = "
    SELECT o.*, p.name as product_name 
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    ORDER BY o.id DESC 
    LIMIT 5
";
$recent_orders = $connection->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Shada</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .stat-card h3 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .recent-orders {
            background: #fff;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .recent-orders h2 {
            margin-bottom: 1rem;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-table th,
        .order-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-table th {
            background: #f8f9fa;
            font-weight: 500;
        }
        
        .status-pending {
            color: #ffc107;
        }
        
        .status-confirmed {
            color: #28a745;
        }
        
        .admin-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .admin-actions .btn {
            flex: 1;
            text-align: center;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Admin Dashboard</h1>
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
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="number"><?php echo $counts['products']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $counts['orders']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Pending Orders</h3>
                <div class="number"><?php echo $counts['pending_orders']; ?></div>
            </div>
        </div>

        <div class="admin-actions">
            <a href="add_product.php" class="btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
            <a href="view_products.php" class="btn">
                <i class="fas fa-boxes"></i> Manage Products
            </a>
            <a href="view_orders.php" class="btn">
                <i class="fas fa-list"></i> View All Orders
            </a>
        </div>

        <div class="recent-orders">
            <h2>Recent Orders</h2>
            <?php if ($recent_orders): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Size</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = $recent_orders->fetchArray(SQLITE3_ASSOC)): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo h($order['customer_name']); ?></td>
                                <td><?php echo h($order['product_name']); ?></td>
                                <td><?php echo h($order['taille']); ?></td>
                                <td>
                                    <span class="status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent orders.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada Admin Panel</p>
        </div>
    </footer>
</body>
</html>
