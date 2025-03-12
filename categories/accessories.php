<?php
session_start();
include '../conn/conn.php';
include '../includes/session.php';
include '../includes/product-functions.php';
include '../includes/cart-functions.php';
include '../includes/favorites-functions.php';

$categoryName = 'accessories';
$products = getProductsByCategory($categoryName);
$categories = getAllCategories();

$cartCount = 0;
$favoritesCount = 0;

if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $cartCount = getCartItemsCount($userId);
    $favoritesCount = getFavoritesCount($userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accessories - Dripforge</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <!---Navbar--->
    <nav class="navbar">
        <ul class="nav-links">
            <li><a href="../main/web.php">Home</a></li>
            <li><a href="../shop.php">Shop</a></li>
            <li><a href="../about.php">About Us</a></li>
            <li><a href="../contact.php">Contact Us</a></li>
        </ul>
    </nav>

    <nav class="navbar2">
        <ul class="nav-links">
            <?php foreach ($categories as $category): ?>
                <li><a href="<?= strtolower($category['category_name']) ?>.php"><?= ucfirst($category['category_name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="category-page">
        <h1 class="page-title">Accessories Collection</h1>
        
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (strpos($product['image_url'], 'http') === 0): ?>
                            <!-- External URL -->
                            <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                        <?php else: ?>
                            <!-- Local path -->
                            <img src="../<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= $product['product_name'] ?></h3>
                        <p><?= substr($product['description'], 0, 60) ?>...</p>
                        <span class="price">$<?= number_format($product['price'], 2) ?></span>
                        <a href="../product.php?id=<?= $product['product_id'] ?>" class="shop-now-btn">View Product</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Address</h3>
                <p>123 Fashion Street</p>
                <p>Style District</p>
                <p>New York, NY 10001</p>
            </div>

            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: info@dripforge.com</p>
                <p>Phone: (555) 123-4567</p>
            </div>

            <div class="footer-section">
                <h3>Feedback</h3>
                <form class="feedback-form">
                    <input type="email" placeholder="Your Email" required>
                    <textarea placeholder="Your Message" required></textarea>
                    <button type="submit">Send Feedback</button>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Dripforge. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>