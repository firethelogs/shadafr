<?php
require_once 'includes/config.php';
require_once 'includes/delivery_fees.php';
// page de remerciement après la commande

$order_details = null;
if (isset($_GET['order_id'])) {
    $order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
    if ($order_id) {
        $stmt = $connection->prepare("
            SELECT o.*, p.name as product_name, p.price_dzd as product_price
            FROM orders o 
            JOIN products p ON o.product_id = p.id 
            WHERE o.id = :id
        ");
        $stmt->bindValue(':id', $order_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $order_details = $result->fetchArray(SQLITE3_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Meta Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '1026551879468585');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=1026551879468585&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merci pour votre commande - Shada</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .thank-you-container {
            text-align: center;
            padding: 3rem 0;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .thank-you-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }
        
        .thank-you-title {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .thank-you-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 2rem;
            max-width: 600px;
            line-height: 1.6;
        }
        
        .thank-you-details {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            max-width: 500px;
            width: 100%;
        }
        
        .thank-you-details h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .thank-you-details p {
            margin: 0.5rem 0;
            font-size: 1rem;
            color: #666;
        }
        
        .order-pricing {
            background: #e8f5e8;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        
        .order-pricing p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
            color: #333;
        }
        
        .order-pricing p:last-child {
            border-top: 2px solid #28a745;
            padding-top: 0.5rem;
            margin-top: 1rem;
            font-size: 1.3rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-home {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-home:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .thank-you-title {
                font-size: 2rem;
            }
            
            .thank-you-message {
                font-size: 1.1rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-home, .btn-secondary {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }
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
        <div class="thank-you-container">
            <div class="thank-you-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1 class="thank-you-title">Merci pour votre commande !</h1>
            
            <p class="thank-you-message">
                Votre commande a été passée avec succès. Notre équipe va traiter votre demande et vous contacter sous peu pour confirmer les détails de la livraison.
            </p>
            
            <div class="thank-you-details">
                <h3><i class="fas fa-info-circle"></i> Détails de votre commande</h3>
                <?php if ($order_details): ?>
                    <p><i class="fas fa-hashtag"></i> Commande #<?php echo $order_details['id']; ?></p>
                    <p><i class="fas fa-box"></i> Produit: <?php echo h($order_details['product_name']); ?></p>
                    <p><i class="fas fa-palette"></i> Couleur: <?php echo h($order_details['color']); ?></p>
                    <p><i class="fas fa-ruler"></i> Taille: <?php echo h($order_details['taille']); ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> Livraison: <?php echo h($order_details['wilaya']); ?></p>
                    
                    <div class="order-pricing">
                        <p><i class="fas fa-tag"></i> Prix du produit: <strong><?php echo number_format($order_details['product_price'], 0); ?> DZD</strong></p>
                        <p><i class="fas fa-truck"></i> Frais de livraison: <strong><?php echo number_format($order_details['delivery_cost'], 0); ?> DZD</strong></p>
                        <p style="font-size: 1.2em; color: #e74c3c;"><i class="fas fa-calculator"></i> Total: <strong><?php echo number_format($order_details['product_price'] + $order_details['delivery_cost'], 0); ?> DZD</strong></p>
                    </div>
                    
                    <?php if ($order_details['noest_tracking_number']): ?>
                        <p><i class="fas fa-barcode"></i> Suivi: <?php echo h($order_details['noest_tracking_number']); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
                
                <h3 style="margin-top: 2rem;"><i class="fas fa-clock"></i> Prochaines étapes</h3>
                <p><i class="fas fa-phone"></i> Nous vous appellerons dans les 24 heures</p>
                <p><i class="fas fa-truck"></i> Confirmation des détails de livraison</p>
                <p><i class="fas fa-credit-card"></i> Paiement à la livraison</p>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn-home">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
                <?php if ($order_details && $order_details['noest_tracking_number']): ?>
                    <a href="tracking.php?tracking=<?php echo urlencode($order_details['noest_tracking_number']); ?>" class="btn-secondary">
                        <i class="fas fa-search"></i> Suivre ma commande
                    </a>
                <?php endif; ?>
                <a href="javascript:history.back()" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Add some animation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.thank-you-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.6s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
