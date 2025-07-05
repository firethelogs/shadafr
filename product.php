<?php
require_once 'includes/config.php';
require_once 'includes/delivery_fees.php';

// Get product ID from URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header("Location: index.php");
    exit;
}

// Fetch product details
$stmt = $connection->prepare("SELECT id, name, description, price_dzd, tailles, main_image FROM products WHERE id = :id");
$stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$result = $stmt->execute();
$product = $result->fetchArray(SQLITE3_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch product gallery images
$stmt = $connection->prepare("SELECT image_path FROM product_images WHERE product_id = :id");
$stmt->bindValue(':id', $product_id, SQLITE3_INTEGER);
$gallery_result = $stmt->execute();

// Get available sizes
$sizes = array_map('trim', explode(',', $product['tailles']));

// Check for success message
$success_message = '';
// Message removed: handled by merci.php now
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
    <title><?php echo h($product['name']); ?> - Shada</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
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
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo h($success_message); ?>
            </div>
        <?php endif; ?>

        <div class="product-details">
            <div class="product-gallery">
                <img src="uploads/products/<?php echo h($product['main_image']); ?>" 
                     alt="<?php echo h($product['name']); ?>" 
                     class="main-image" 
                     id="mainImage">
                
                <div class="image-grid">
                    <img src="uploads/products/<?php echo h($product['main_image']); ?>" 
                         alt="Main Image"
                         onclick="updateMainImage(this.src)"
                         class="thumbnail">
                    <?php while($img = $gallery_result->fetchArray(SQLITE3_ASSOC)): ?>
                        <img src="uploads/products/<?php echo h($img['image_path']); ?>" 
                             alt="Product Image"
                             onclick="updateMainImage(this.src)"
                             class="thumbnail">
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="product-info">
                <h2><?php echo h($product['name']); ?></h2>
                <?php if (!empty($product['description'])): ?>
                    <p class="product-description"><?php echo h($product['description']); ?></p>
                <?php endif; ?>
                
                <form action="process_order.php" method="POST" class="order-form">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label for="customer_name">Nom complet :</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Numéro de téléphone :</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Adresse de livraison :</label>
                        <textarea id="address" name="address" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="taille">Taille :</label>
                        <select id="taille" name="taille" class="form-control" required>
                            <option value="Standard">Standard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Choisissez la couleur :</label>
                        <select id="color" name="color" class="form-control" required>
                            <option value="">Choisissez une couleur</option>
                            <option value="Noir">Noir</option>
                            <option value="Beige">Beige</option>
                            <option value="Rose clair">Rose clair</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Option de livraison :</label>
                        <div class="delivery-options">
                            <label class="radio-label">
                                <input type="radio" name="delivery_choice" value="domicile" checked onchange="updateDeliveryFee()">
                                <span>Livraison à domicile</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="delivery_choice" value="bureau_noest" onchange="updateDeliveryFee()">
                                <span>Bureau Noest (Stop Desk)</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="wilaya">Wilaya :</label>
                        <select id="wilaya" name="wilaya" class="form-control" required onchange="updateDeliveryFee()">
                            <option value="">Choisissez votre Wilaya</option>
                            <?php
                            $wilayas = DeliveryFees::getAllWilayas();
                            foreach ($wilayas as $wilayaId => $wilayaData) {
                                echo "<option value=\"{$wilayaId}\">{$wilayaId}- {$wilayaData['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="price-section">
                        <p class="price">Prix du produit: <span id="productPrice"><?php echo number_format($product['price_dzd'], 0); ?> DZD</span></p>
                        <p class="delivery-fee">Frais de livraison: <span id="deliveryFee">0 DZD</span></p>
                        <div class="total-price">
                            <strong>Total: <span id="totalPrice"><?php echo number_format($product['price_dzd'], 0); ?> DZD</span></strong>
                        </div>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-shopping-cart"></i> Commander
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Delivery fees data
        const deliveryFees = <?php echo json_encode(DeliveryFees::getAllWilayas()); ?>;
        const productPrice = <?php echo $product['price_dzd']; ?>;
        
        function updateMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
        
        function updateDeliveryFee() {
            const wilayaSelect = document.getElementById('wilaya');
            const deliveryChoice = document.querySelector('input[name="delivery_choice"]:checked');
            
            if (wilayaSelect.value && deliveryChoice) {
                const wilayaId = wilayaSelect.value;
                const deliveryType = deliveryChoice.value === 'domicile' ? 'domicile' : 'stopdesk';
                
                let deliveryFee = 0;
                if (deliveryFees[wilayaId]) {
                    deliveryFee = deliveryFees[wilayaId][deliveryType];
                }
                
                const totalPrice = productPrice + deliveryFee;
                
                // Update the display
                document.getElementById('deliveryFee').textContent = deliveryFee.toLocaleString() + ' DZD';
                document.getElementById('totalPrice').textContent = totalPrice.toLocaleString() + ' DZD';
                
                // Add visual feedback
                const deliveryFeeElement = document.getElementById('deliveryFee');
                deliveryFeeElement.style.color = deliveryFee > 0 ? '#e74c3c' : '#27ae60';
                
                // Show delivery info
                const wilayaText = wilayaSelect.options[wilayaSelect.selectedIndex].text;
                const deliveryText = deliveryChoice.value === 'domicile' ? 'Livraison à domicile' : 'Bureau Noest (Stop Desk)';
                
                // Update or create delivery info display
                let deliveryInfo = document.getElementById('deliveryInfo');
                if (!deliveryInfo) {
                    deliveryInfo = document.createElement('div');
                    deliveryInfo.id = 'deliveryInfo';
                    deliveryInfo.className = 'delivery-info';
                    document.querySelector('.price-section').appendChild(deliveryInfo);
                }
                
                deliveryInfo.innerHTML = `
                    <small>
                        <i class="fas fa-truck"></i> ${deliveryText} vers ${wilayaText}
                    </small>
                `;
            } else {
                // Reset to default
                document.getElementById('deliveryFee').textContent = '0 DZD';
                document.getElementById('totalPrice').textContent = productPrice.toLocaleString() + ' DZD';
                
                const deliveryInfo = document.getElementById('deliveryInfo');
                if (deliveryInfo) {
                    deliveryInfo.remove();
                }
            }
        }
        
        // Initialize delivery fee calculation when page loads
        document.addEventListener('DOMContentLoaded', function() {
            updateDeliveryFee();
        });
    </script>
</body>
</html>
