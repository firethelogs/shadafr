<?php
require_once 'includes/config.php';

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
if (isset($_GET['order']) && $_GET['order'] === 'success') {
    $success_message = 'Your order has been placed successfully! We will contact you soon.';
}
?>
<!DOCTYPE html>
<html lang="en">
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
                <p class="price">DZD <?php echo number_format($product['price_dzd'], 2); ?></p>
                
                <form action="process_order.php" method="POST" class="order-form">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    
                    <div class="form-group">
                        <label for="customer_name">Full Name:</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Delivery Address:</label>
                        <textarea id="address" name="address" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="taille">Size:</label>
                        <select id="taille" name="taille" class="form-control" required>
                            <option value="Standard">Standard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="color">Select Color:</label>
                        <select id="color" name="color" class="form-control" required>
                            <option value="">Choose a color</option>
                            <option value="Noir">Noir</option>
                            <option value="Beige">Beige</option>
                            <option value="Rose clair">Rose clair</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Delivery Option:</label>
                        <div class="delivery-options">
                            <label class="radio-label">
                                <input type="radio" name="delivery_choice" value="domicile" checked>
                                <span>Domicile</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="delivery_choice" value="bureau_noest">
                                <span>Bureau Noest</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="wilaya">Select Wilaya:</label>
                        <select id="wilaya" name="wilaya" class="form-control" required>
                            <option value="">Choose your Wilaya</option>
                            <?php
                            $wilayas = [
                                "1- Adrar", "2- Chlef", "3- Laghouat", "4- Oum El Bouaghi", "5- Batna",
                                "6- Béjaïa", "7- Biskra", "8- Béchar", "9- Blida", "10- Bouira",
                                "11- Tamanrasset", "12- Tébessa", "13- Tlemcen", "14- Tiaret", "15- Tizi-Ouzou",
                                "16- Algiers", "17- Djelfa", "18- Jijel", "19- Sétif", "20- Saïda",
                                "21- Skikda", "22- Sidi Bel Abbès", "23- Annaba", "24- Guelma", "25- Constantine",
                                "26- Médéa", "27- Mostaganem", "28- M'Sila", "29- Mascara", "30- Ouargla",
                                "31- Oran", "32- El Bayadh", "33- Illizi", "34- Bordj Bou Arreridj", "35- Boumerdès",
                                "36- El Tarf", "37- Tindouf", "38- Tissemsilt", "39- El Oued", "40- Khenchela",
                                "41- Souk Ahras", "42- Tipaza", "43- Mila", "44- Aïn Defla", "45- Naâma",
                                "46- Aïn Témouchent", "47- Ghardaïa", "48- Relizane", "49- Timimoun", "50- Bordj Badji Mokhtar",
                                "51- Ouled Djellal", "52- Béni Abbès", "53- In Salah", "54- In Guezzam", "55- Touggourt",
                                "56- Djanet", "57- El MGhair", "58- El Meniaa"
                            ];
                            foreach ($wilayas as $wilaya) {
                                echo "<option value=\"" . h($wilaya) . "\">" . h($wilaya) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function updateMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
    </script>
</body>
</html>
