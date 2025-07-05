<?php
// page de remerciement après la commande
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
        }
        
        .thank-you-details p {
            margin-bottom: 0.5rem;
            color: #555;
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
                <h3><i class="fas fa-info-circle"></i> Prochaines étapes</h3>
                <p><i class="fas fa-phone"></i> Nous vous appellerons dans les 24 heures</p>
                <p><i class="fas fa-truck"></i> Confirmation des détails de livraison</p>
                <p><i class="fas fa-credit-card"></i> Paiement à la livraison</p>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn-home">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
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
