<?php
session_start();
include 'conn/conn.php';
include 'includes/session.php';
include 'includes/product-functions.php';
include 'includes/cart-functions.php';
include 'includes/favorites-functions.php';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: main/web.php');
    exit;
}

$productId = $_GET['id'];
$product = getProductById($productId);

if (!$product) {
    header('Location: main/web.php');
    exit;
}

$relatedProducts = getRelatedProducts($productId, $product['category_id'], 4);
$categories = getAllCategories();

$cartCount = 0;
$favoritesCount = 0;
$isInFavorites = false;

if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $cartCount = getCartItemsCount($userId);
    $favoritesCount = getFavoritesCount($userId);
    $isInFavorites = isInFavorites($userId, $productId);
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && isLoggedIn()) {
    $quantity = $_POST['quantity'] ?? 1;
    addToCart($userId, $productId, $quantity);
    header("Location: product.php?id=$productId&added=1");
    exit;
}

// Handle add to favorites
if (isset($_GET['add_to_favorites']) && isLoggedIn()) {
    addToFavorites($userId, $productId);
    header("Location: product.php?id=$productId&favorited=1");
    exit;
}

// Handle remove from favorites
if (isset($_GET['remove_from_favorites']) && isLoggedIn()) {
    // We need to get the favorite ID first
    $stmt = $conn->prepare("SELECT favorite_id FROM `favorites` WHERE `user_id` = :user_id AND `product_id` = :product_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();
    
    if ($favorite = $stmt->fetch(PDO::FETCH_ASSOC)) {
        removeFromFavorites($favorite['favorite_id']);
    }
    
    header("Location: product.php?id=$productId&unfavorited=1");
    exit;
}

$successMessage = '';
if (isset($_GET['added']) && $_GET['added'] == 1) {
    $successMessage = 'Product added to cart successfully!';
}
if (isset($_GET['favorited']) && $_GET['favorited'] == 1) {
    $successMessage = 'Product added to favorites successfully!';
}
if (isset($_GET['unfavorited']) && $_GET['unfavorited'] == 1) {
    $successMessage = 'Product removed from favorites successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product['product_name'] ?> - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .product-detail {
            max-width: 1200px;
            margin: 120px auto 50px;
            display: flex;
            gap: 50px;
            padding: 0 20px;
        }
        
        .product-image-container {
            flex: 1;
            background-color: #f9f9f9;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .product-info-container {
            flex: 1;
            padding: 20px;
        }
        
        .product-title {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: 600;
            color: #007bff;
            margin-bottom: 20px;
        }
        
        .product-description {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #666;
            margin-bottom: 30px;
        }
        
        .stock-info {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .in-stock {
            color: green;
            font-weight: 600;
        }
        
        .out-of-stock {
            color: red;
            font-weight: 600;
        }
        
        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            height: 50px;
        }
        
        .quantity-selector button {
            width: 40px;
            height: 40px;
            background: #eee;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .quantity-selector input {
            width: 60px;
            height: 40px;
            text-align: center;
            font-size: 1rem;
            border: 1px solid #ddd;
        }
        
        .add-to-cart-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-grow: 1;
        }
        
        .add-to-cart-btn:hover {
            background: #0056b3;
        }
        
        .heart-btn {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #ddd;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .heart-btn:hover {
            background: #f9f9f9;
        }
        
        .heart-btn.active {
            color: red;
        }
        
        .category-tag {
            display: inline-block;
            padding: 5px 10px;
            background: #f1f1f1;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .related-products {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .related-products h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .icon-container {
            position: fixed;
            top: 20px;
            right: 300px;
            z-index: 1000;
            display: flex;
            gap: 15px;
        }
        
        .icon-badge {
            position: relative;
            display: inline-block;
        }
        
        .icon-badge i {
            font-size: 24px;
            color: white;
        }
        
        .badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
        }
        
        .user-menu {
            position: fixed;
            top: 20px;
            right: 180px;
            z-index: 1000;
        }
        
        .user-menu .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .user-menu .dropdown-toggle {
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .user-menu .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 4px;
            padding: 5px 0;
        }
        
        .user-menu .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .user-menu .dropdown-menu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        
        .user-menu .dropdown-menu a:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>

<body>
    <!---Navbar--->
    <nav class="navbar">
        <ul class="nav-links">
            <li><a href="main/web.php">Home</a></li>
            <li><a href="shop.php">Shop</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
    </nav>

    <nav class="navbar2">
        <ul class="nav-links">
            <?php foreach ($categories as $category): ?>
                <li><a href="categories/<?= strtolower($category['category_name']) ?>.php"><?= ucfirst($category['category_name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Icons Container -->
    <div class="icon-container">
        <!-- Cart Icon -->
        <a href="cart.php" class="icon-badge">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cartCount > 0): ?>
                <span class="badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        
        <!-- Favorites Icon -->
        <a href="favorites.php" class="icon-badge">
            <i class="fas fa-heart"></i>
            <?php if ($favoritesCount > 0): ?>
                <span class="badge"><?= $favoritesCount ?></span>
            <?php endif; ?>
        </a>
    </div>
    
    <!-- User Menu -->
    <div class="user-menu">
        <div class="dropdown">
            <?php if (isLoggedIn()): ?>
                <a href="#" class="dropdown-toggle">
                    <i class="fas fa-user"></i>
                    <?= $_SESSION['first_name'] ?>
                </a>
                <div class="dropdown-menu">
                    <a href="profile.php">My Profile</a>
                    <a href="orders.php">My Orders</a>
                    <a href="logout.php">Logout</a>
                </div>
            <?php else: ?>
                <a href="index.php" class="dropdown-toggle">
                    <i class="fas fa-user"></i>
                    Login/Register
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-detail">
        <?php if (!empty($successMessage)): ?>
            <div class="success-message">
                <?= $successMessage ?>
            </div>
        <?php endif; ?>
        
        <div class="product-image-container">
            <img src="<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
        </div>
        
        <div class="product-info-container">
            <span class="category-tag"><?= ucfirst($product['category_name']) ?></span>
            <h1 class="product-title"><?= $product['product_name'] ?></h1>
            <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
            <p class="product-description"><?= $product['description'] ?></p>
            
            <p class="stock-info">
                <?php if ($product['stock'] > 0): ?>
                    <span class="in-stock">In Stock (<?= $product['stock'] ?> available)</span>
                <?php else: ?>
                    <span class="out-of-stock">Out of Stock</span>
                <?php endif; ?>
            </p>
            
            <?php if ($product['stock'] > 0): ?>
                <form method="post" action="product.php?id=<?= $productId ?>">
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button type="button" onclick="decrementQuantity()">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button type="button" onclick="incrementQuantity()">+</button>
                        </div>
                        
                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        
                        <?php if (isLoggedIn()): ?>
                            <?php if ($isInFavorites): ?>
                                <a href="product.php?id=<?= $productId ?>&remove_from_favorites=1" class="heart-btn active">
                                    <i class="fas fa-heart"></i>
                                </a>
                            <?php else: ?>
                                <a href="product.php?id=<?= $productId ?>&add_to_favorites=1" class="heart-btn">
                                    <i class="far fa-heart"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="index.php" class="heart-btn" title="Login to add to favorites">
                                <i class="far fa-heart"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($relatedProducts)): ?>
        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?= $relatedProduct['image_url'] ?>" alt="<?= $relatedProduct['product_name'] ?>">
                        </div>
                        <div class="product-info">
                            <h3><?= $relatedProduct['product_name'] ?></h3>
                            <p><?= substr($relatedProduct['description'], 0, 60) ?>...</p>
                            <span class="price">$<?= number_format($relatedProduct['price'], 2) ?></span>
                            <a href="product.php?id=<?= $relatedProduct['product_id'] ?>" class="shop-now-btn">View Product</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

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

    <script>
        function incrementQuantity() {
            const quantityInput = document.getElementById('quantity');
            const maxStock = <?= $product['stock'] ?>;
            let currentValue = parseInt(quantityInput.value);
            
            if (currentValue < maxStock) {
                quantityInput.value = currentValue + 1;
            }
        }
        
        function decrementQuantity() {
            const quantityInput = document.getElementById('quantity');
            let currentValue = parseInt(quantityInput.value);
            
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        }
    </script>
</body>
</html>