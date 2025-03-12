<?php
session_start();
include 'conn/conn.php';
include 'includes/session.php';
include 'includes/product-functions.php';
include 'includes/cart-functions.php';
include 'includes/favorites-functions.php';
include 'includes/order-functions.php';

// Redirect to login if not logged in
requireLogin();

$userId = getCurrentUserId();
$cartCount = getCartItemsCount($userId);
$favoritesCount = getFavoritesCount($userId);
$categories = getAllCategories();

// Check if order ID is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Location: main/web.php');
    exit;
}

$orderId = $_GET['order_id'];
$order = getOrderById($orderId, $userId);

if (!$order) {
    header('Location: main/web.php');
    exit;
}

// Parse shipping address
$shippingAddress = json_decode($order['shipping_address'], true);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .confirmation-page {
            max-width: 800px;
            margin: 120px auto 50px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        
        .confirmation-message {
            text-align: center;
            padding: 20px;
            margin-bottom: 30px;
            background-color: #d4edda;
            color: #155724;
            border-radius: 5px;
        }
        
        .confirmation-message i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #28a745;
        }
        
        .confirmation-message h2 {
            margin-bottom: 10px;
            color: #155724;
        }
        
        .order-details {
            margin-bottom: 30px;
        }
        
        .order-details h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .detail-label {
            flex: 0 0 150px;
            font-weight: 600;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .order-items {
            margin-bottom: 30px;
        }
        
        .order-items h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-details {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
        }
        
        .item-price {
            color: #888;
            margin-top: 5px;
        }
        
        .item-quantity {
            color: #888;
            margin: 0 10px;
        }
        
        .item-total {
            font-weight: 600;
            color: #333;
        }
        
        .order-totals {
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        
        .order-totals h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .total-label {
            font-weight: 600;
            color: #555;
        }
        
        .grand-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #ddd;
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
        }
        
        .shipping-info {
            margin-bottom: 30px;
        }
        
        .shipping-info h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            color: #333;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .continue-shopping {
            background-color: #007bff;
            color: white;
        }
        
        .continue-shopping:hover {
            background-color: #0056b3;
        }
        
        .view-orders {
            background-color: #f1f1f1;
            color: #333;
        }
        
        .view-orders:hover {
            background-color: #ddd;
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

    <div class="confirmation-page">
        <div class="confirmation-message">
            <i class="fas fa-check-circle"></i>
            <h2>Thank You for Your Order!</h2>
            <p>Your order has been successfully placed. We'll process it right away!</p>
        </div>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <div class="detail-row">
                <div class="detail-label">Order Number:</div>
                <div class="detail-value">#<?= $order['order_id'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Date:</div>
                <div class="detail-value"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payment Method:</div>
                <div class="detail-value"><?= ucfirst($order['payment_method']) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Payment Status:</div>
                <div class="detail-value"><?= ucfirst($order['payment_status']) ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Order Status:</div>
                <div class="detail-value"><?= ucfirst($order['order_status']) ?></div>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Order Items</h3>
            <?php foreach ($order['items'] as $item): ?>
                <div class="order-item">
                    <div class="item-details">
                        <div class="item-image">
                            <img src="<?= $item['image_url'] ?>" alt="<?= $item['product_name'] ?>">
                        </div>
                        <div>
                            <div class="item-name"><?= $item['product_name'] ?></div>
                            <div class="item-price">$<?= number_format($item['price'], 2) ?> each</div>
                        </div>
                    </div>
                    <div class="item-quantity">x<?= $item['quantity'] ?></div>
                    <div class="item-total">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="order-totals">
            <h3>Order Summary</h3>
            <div class="totals-row">
                <div class="total-label">Subtotal</div>
                <div>$<?= number_format($order['total_amount'], 2) ?></div>
            </div>
            <div class="totals-row">
                <div class="total-label">Shipping</div>
                <div>Free</div>
            </div>
            <div class="totals-row grand-total">
                <div class="total-label">Total</div>
                <div>$<?= number_format($order['total_amount'], 2) ?></div>
            </div>
        </div>
        
        <div class="shipping-info">
            <h3>Shipping Information</h3>
            <div class="detail-row">
                <div class="detail-label">Name:</div>
                <div class="detail-value"><?= $shippingAddress['name'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value"><?= $shippingAddress['address'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">City:</div>
                <div class="detail-value"><?= $shippingAddress['city'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">State:</div>
                <div class="detail-value"><?= $shippingAddress['state'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">ZIP Code:</div>
                <div class="detail-value"><?= $shippingAddress['zip'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Phone:</div>
                <div class="detail-value"><?= $shippingAddress['phone'] ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value"><?= $shippingAddress['email'] ?></div>
            </div>
        </div>
        
        <div class="actions">
            <a href="main/web.php" class="action-btn continue-shopping">Continue Shopping</a>
            <a href="orders.php" class="action-btn view-orders">View All Orders</a>
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