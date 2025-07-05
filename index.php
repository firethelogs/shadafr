<?php
require_once 'includes/config.php';

// Fetch all products TTO JAXX
$query = "SELECT id, name, description, price_dzd, main_image FROM products ORDER BY id DESC";
$result = $connection->query($query);
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
    <title>Shada - Ecommerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Shada</h1>
        </div>
    </header>

    <main class="container">
        <div class="product-grid">
            <?php 
            $hasProducts = false;
            while ($product = $result->fetchArray(SQLITE3_ASSOC)): 
                $hasProducts = true;
            ?>
                <div class="product-card">
                    <img src="uploads/products/<?php echo h($product['main_image']); ?>" 
                         alt="<?php echo h($product['name']); ?>">
                    <div class="product-info">
                        <h2><?php echo h($product['name']); ?></h2>
                        <?php if (!empty($product['description'])): ?>
                            <p class="description"><?php echo h($product['description']); ?></p>
                        <?php endif; ?>
                        <p class="price">DZD <?php echo number_format($product['price_dzd'], 2); ?></p>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">
                            View Details
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (!$hasProducts): ?>
                <div class="no-products">
                    <p>No products available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Shada. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
