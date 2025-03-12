<?php
session_start();
include 'conn/conn.php';
include 'includes/session.php';
include 'includes/product-functions.php';
include 'includes/cart-functions.php';
include 'includes/favorites-functions.php';

// Redirect to login if not logged in
requireLogin();

$userId = getCurrentUserId();
$favorites = getFavorites($userId);
$cartCount = getCartItemsCount($userId);
$favoritesCount = getFavoritesCount($userId);
$categories = getAllCategories();

// Handle remove from favorites
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $favoriteId = $_GET['remove'];
    removeFromFavorites($favoriteId);
    header('Location: favorites.php?removed=1');
    exit;
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    addToCart($userId, $productId, $quantity);
    header('Location: favorites.php?added=1');
    exit;
}

$message = '';
if (isset($_GET['removed']) && $_GET['removed'] == 1) {
    $message = 'Product removed from favorites!';
}
if (isset($_GET['added']) && $_GET['added'] == 1) {
    $message = 'Product added to cart successfully!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .favorites-page {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }
        
        .favorites-empty {
            text-align: center;
            padding: 50px 0;
        }
        
        .favorites-empty i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .favorites-empty p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }
        
        .favorite-item {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .favorite-item:hover {
            transform: translateY(-5px);
        }
        
        .favorite-image {
            width: 100%;
            height: 300px;
            overflow: hidden;
        }
        
        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .favorite-details {
            padding: 20px;
            text-align: center;
        }
        
        .favorite-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }
        
        .favorite-price {
            font-size: 1.3rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        
        .favorite-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .add-to-cart-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .add-to-cart-btn:hover {
            background-color: #0056b3;
        }
        
        .remove-favorite-btn {
            color: #dc3545;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }
        
        .remove-favorite-btn:hover {
            color: #c82333;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .quantity-selector button {
            background: #f1f1f1;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .quantity-selector input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
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

    <div class="favorites-page">
        <h1 class="page-title">My Favorites</h1>
        
        <?php if (!empty($message)): ?>
            <div class="success-message">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($favorites)): ?>
            <div class="favorites-empty">
                <i class="fas fa-heart-broken"></i>
                <p>Your favorites list is empty</p>
                <a href="main/web.php" class="shop-now-btn" style="display:inline-block;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="favorites-grid">
                <?php foreach ($favorites as $favorite): ?>
                    <div class="favorite-item">
                        <div class="favorite-image">
                            <?php if (strpos($favorite['image_url'], 'http') === 0): ?>
                                <img src="<?= $favorite['image_url'] ?>" alt="<?= $favorite['product_name'] ?>">
                            <?php else: ?>
                                <img src="<?= $favorite['image_url'] ?>" alt="<?= $favorite['product_name'] ?>">
                            <?php endif; ?>
                        </div>
                        
                        <div class="favorite-details">
                            <h3 class="favorite-name"><?= $favorite['product_name'] ?></h3>
                            <p class="favorite-price">$<?= number_format($favorite['price'], 2) ?></p>
                            
                            <form method="post" action="favorites.php">
                                <input type="hidden" name="product_id" value="<?= $favorite['product_id'] ?>">
                                <div class="favorite-actions">
                                    <div class="quantity-selector">
                                        <button type="button" onclick="decrementQuantity(this)">-</button>
                                        <input type="number" name="quantity" value="1" min="1" max="<?= $favorite['stock'] ?>">
                                        <button type="button" onclick="incrementQuantity(this)">+</button>
                                    </div>
                                    
                                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                    
                                    <a href="favorites.php?remove=<?= $favorite['favorite_id'] ?>" class="remove-favorite-btn" onclick="return confirm('Remove from favorites?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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

    <script>
        function incrementQuantity(button) {
            const input = button.previousElementSibling;
            const max = parseInt(input.getAttribute('max'));
            let value = parseInt(input.value);
            
            if (value < max) {
                input.value = value + 1;
            }
        }
        
        function decrementQuantity(button) {
            const input = button.nextElementSibling;
            let value = parseInt(input.value);
            
            if (value > 1) {
                input.value = value - 1;
            }
        }
    </script>
</body>
</html>