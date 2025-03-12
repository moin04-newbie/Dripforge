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
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);
$cartCount = getCartItemsCount($userId);
$favoritesCount = getFavoritesCount($userId);
$categories = getAllCategories();

// Handle update cart
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $cartId => $quantity) {
        updateCartItem($cartId, $quantity);
    }
    header('Location: cart.php?updated=1');
    exit;
}

// Handle remove item
if (isset($_GET['remove']) && !empty($_GET['remove'])) {
    $cartId = $_GET['remove'];
    removeFromCart($cartId);
    header('Location: cart.php?removed=1');
    exit;
}

$message = '';
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $message = 'Cart updated successfully!';
}
if (isset($_GET['removed']) && $_GET['removed'] == 1) {
    $message = 'Item removed from cart!';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .cart-page {
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
        
        .cart-empty {
            text-align: center;
            padding: 50px 0;
        }
        
        .cart-empty i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .cart-empty p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-table th {
            padding: 12px;
            text-align: left;
            background-color: #f9f9f9;
            border-bottom: 2px solid #ddd;
        }
        
        .cart-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .product-thumbnail {
            width: 100px;
            height: 100px;
            overflow: hidden;
            border-radius: 5px;
        }
        
        .product-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .cart-actions {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .update-cart-btn {
            background-color: #f1f1f1;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .update-cart-btn:hover {
            background-color: #ddd;
        }
        
        .continue-shopping {
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-right: 20px;
        }
        
        .continue-shopping:hover {
            text-decoration: underline;
        }
        
        .cart-totals {
            width: 350px;
            margin-left: auto;
            margin-top: 50px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        
        .cart-totals h3 {
            margin-bottom: 20px;
            color: #333;
        }
        
        .cart-totals-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .cart-totals-table tr {
            border-bottom: 1px solid #ddd;
        }
        
        .cart-totals-table th {
            text-align: left;
            padding: 10px 0;
        }
        
        .cart-totals-table td {
            text-align: right;
            padding: 10px 0;
        }
        
        .checkout-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        
        .checkout-btn:hover {
            background-color: #0056b3;
        }
        
        .remove-item {
            color: #dc3545;
            text-decoration: none;
            font-size: 1.2rem;
        }
        
        .remove-item:hover {
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

    <div class="cart-page">
        <h1 class="page-title">Shopping Cart</h1>
        
        <?php if (!empty($message)): ?>
            <div class="success-message">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart"></i>
                <p>Your cart is empty</p>
                <a href="main/web.php" class="shop-now-btn" style="display:inline-block;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form action="cart.php" method="post">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <div class="product-thumbnail">
                                            <img src="<?= $item['image_url'] ?>" alt="<?= $item['product_name'] ?>">
                                        </div>
                                        <span class="product-name"><?= $item['product_name'] ?></span>
                                    </div>
                                </td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" class="quantity-input" name="quantity[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
                                </td>
                                <td>$<?= number_format($item['total_price'], 2) ?></td>
                                <td>
                                    <a href="cart.php?remove=<?= $item['cart_id'] ?>" class="remove-item" onclick="return confirm('Are you sure you want to remove this item?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <div>
                        <a href="main/web.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <button type="submit" name="update_cart" class="update-cart-btn">
                            <i class="fas fa-sync-alt"></i> Update Cart
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="cart-totals">
                <h3>Cart Totals</h3>
                <table class="cart-totals-table">
                    <tr>
                        <th>Subtotal</th>
                        <td>$<?= number_format($cartTotal, 2) ?></td>
                    </tr>
                    <tr>
                        <th>Shipping</th>
                        <td>Free</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>$<?= number_format($cartTotal, 2) ?></td>
                    </tr>
                </table>
                
                <a href="checkout.php" class="checkout-btn">
                    Proceed to Checkout
                </a>
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
</body>
</html>