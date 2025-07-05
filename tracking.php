<?php
require_once 'includes/config.php';
require_once 'includes/noest_config.php';

$tracking_info = null;
$error = '';

if (isset($_GET['tracking']) && !empty($_GET['tracking'])) {
    $tracking_number = htmlspecialchars(trim($_GET['tracking']));
    
    try {
        // Get order from database
        $stmt = $connection->prepare("
            SELECT o.*, p.name as product_name 
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            WHERE o.noest_tracking_number = :tracking
        ");
        $stmt->bindValue(':tracking', $tracking_number, SQLITE3_TEXT);
        $result = $stmt->execute();
        $order = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($order) {
            // Try to get updated tracking info from Noest API
            try {
                $api_tracking = $noestAPI->getTracking($tracking_number);
                $tracking_info = [
                    'order' => $order,
                    'tracking' => $api_tracking
                ];
            } catch (Exception $e) {
                // If API fails, show database info
                $tracking_info = [
                    'order' => $order,
                    'tracking' => null
                ];
            }
        } else {
            $error = 'Numéro de suivi non trouvé.';
        }
    } catch (Exception $e) {
        $error = 'Erreur lors de la recherche du suivi.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivre ma commande - Shada</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .tracking-form {
            margin-bottom: 2rem;
        }
        
        .tracking-form input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .order-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
        }
        
        .tracking-timeline {
            position: relative;
            padding-left: 2rem;
        }
        
        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 0.25rem;
            width: 1rem;
            height: 1rem;
            background: #6c757d;
            border-radius: 50%;
            border: 2px solid #fff;
        }
        
        .timeline-item.active::before {
            background: var(--primary-color);
        }
        
        .timeline-item.completed::before {
            background: #28a745;
        }
        
        .timeline-content {
            margin-left: 1rem;
        }
        
        .timeline-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
        }
        
        .timeline-time {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-pending { background: #ffeaa7; color: #2d3436; }
        .status-created { background: #74b9ff; color: white; }
        .status-picked-up { background: #fdcb6e; color: white; }
        .status-in-transit { background: #e17055; color: white; }
        .status-delivered { background: #00b894; color: white; }
        .status-failed { background: #d63031; color: white; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>
                <a href="index.php" style="text-decoration: none; color: inherit;">Shada</a>
            </h1>
        </div>
    </header>

    <main class="container">
        <div class="tracking-container">
            <h2><i class="fas fa-search"></i> Suivre ma commande</h2>
            
            <form method="GET" class="tracking-form">
                <input type="text" name="tracking" placeholder="Entrez votre numéro de suivi" 
                       value="<?php echo isset($_GET['tracking']) ? h($_GET['tracking']) : ''; ?>" required>
                <button type="submit" class="btn">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo h($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($tracking_info): ?>
                <div class="order-details">
                    <h3>Détails de la commande</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Commande #:</strong> <?php echo $tracking_info['order']['id']; ?></p>
                            <p><strong>Produit:</strong> <?php echo h($tracking_info['order']['product_name']); ?></p>
                            <p><strong>Client:</strong> <?php echo h($tracking_info['order']['customer_name']); ?></p>
                            <p><strong>Téléphone:</strong> <?php echo h($tracking_info['order']['phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Adresse:</strong> <?php echo h($tracking_info['order']['address']); ?></p>
                            <p><strong>Wilaya:</strong> <?php echo h($tracking_info['order']['wilaya']); ?></p>
                            <p><strong>Statut:</strong> 
                                <span class="status-badge status-<?php echo $tracking_info['order']['delivery_status']; ?>">
                                    <?php echo h($tracking_info['order']['delivery_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="tracking-timeline">
                    <h3>Suivi de la livraison</h3>
                    
                    <div class="timeline-item completed">
                        <div class="timeline-content">
                            <div class="timeline-title">Commande créée</div>
                            <div class="timeline-time">
                                <?php echo date('d/m/Y H:i', strtotime($tracking_info['order']['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($tracking_info['order']['delivery_status'] !== 'pending'): ?>
                        <div class="timeline-item completed">
                            <div class="timeline-content">
                                <div class="timeline-title">Livraison créée</div>
                                <div class="timeline-time">En cours de traitement</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($tracking_info['order']['delivery_status'], ['picked-up', 'in-transit', 'delivered'])): ?>
                        <div class="timeline-item completed">
                            <div class="timeline-content">
                                <div class="timeline-title">Colis récupéré</div>
                                <div class="timeline-time">Le colis a été récupéré par le livreur</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (in_array($tracking_info['order']['delivery_status'], ['in-transit', 'delivered'])): ?>
                        <div class="timeline-item completed">
                            <div class="timeline-content">
                                <div class="timeline-title">En transit</div>
                                <div class="timeline-time">Le colis est en route vers vous</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($tracking_info['order']['delivery_status'] === 'delivered'): ?>
                        <div class="timeline-item completed">
                            <div class="timeline-content">
                                <div class="timeline-title">Livré</div>
                                <div class="timeline-time">Le colis a été livré avec succès</div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($tracking_info['tracking'] && isset($tracking_info['tracking']['events'])): ?>
                        <?php foreach ($tracking_info['tracking']['events'] as $event): ?>
                            <div class="timeline-item completed">
                                <div class="timeline-content">
                                    <div class="timeline-title"><?php echo h($event['status']); ?></div>
                                    <div class="timeline-time">
                                        <?php echo date('d/m/Y H:i', strtotime($event['date'])); ?>
                                        <?php if (!empty($event['location'])): ?>
                                            - <?php echo h($event['location']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" class="btn">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>
