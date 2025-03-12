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
$orders = getUserOrders($userId);

// Handle cancel order
if (isset($_GET['cancel']) && !empty($_GET['cancel'])) {
    $orderId = $_GET['cancel'];
    $result = cancelOrder($orderId, $userId);
    
    if ($result['success']) {
        header('Location: orders.php?cancelled=1');
    } else {
        header('Location: orders.php?error=' . urlencode($result['message']));
    }
    exit;
}

$message = '';
if (isset($_GET['cancelled']) && $_GET['cancelled'] == 1) {
    $message = 'Order has been cancelled successfully!';
}
if (isset($_GET['error'])) {
    $message = $_GET['error'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .orders-page {
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
        
        .orders-empty {
            text-align: center;
            padding: 50px 0;
        }
        
        .orders-empty i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .orders-empty p {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .order-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .order-header {
            background-color: #f9f9f9;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .order-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .order-date {
            color: #888;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .order-label {
            font-weight: 600;
            color: #555;
        }
        
        .order-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: white;
        }
        
        .status-processing {
            background-color: #17a2b8;
        }
        
        .status-shipped {
            background-color: #28a745;
        }
        
        .status-delivered {
            background-color: #28a745;
        }
        
        .status-cancelled {
            background-color: #dc3545;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #333;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .order-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .view-btn {
            background-color: #007bff;
            color: white;
        }
        
        .view-btn:hover {
            background-color: #0056b3;
        }
        
        .cancel-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .cancel-btn:hover {
            background-color: #c82333;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
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

    <div class="orders-page">
        <h1 class="page-title">My Orders</h1>
        
        <?php if (!empty($message)): ?>
            <?php if (strpos($message, 'cancelled') !== false): ?>
                <div class="success-message">
                    <?= $message ?>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <?= $message ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div class="orders-empty">
                <i class="fas fa-box-open"></i>
                <p>You haven't placed any orders yet</p>
                <a href="main/web.php" class="shop-now-btn" style="display:inline-block;">Shop Now</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-number">Order #<?= $order['order_id'] ?></div>
                        <div class="order-date"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-row">
                            <div class="order-label">Total Amount:</div>
                            <div>$<?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                        
                        <div class="order-row">
                            <div class="order-label">Payment Method:</div>
                            <div><?= ucfirst($order['payment_method']) ?></div>
                        </div>
                        
                        <div class="order-row">
                            <div class="order-label">Payment Status:</div>
                            <div><?= ucfirst($order['payment_status']) ?></div>
                        </div>
                        
                        <div class="order-row">
                            <div class="order-label">Order Status:</div>
                            <div>
                                <span class="order-status status-<?= strtolower($order['order_status']) ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="order-actions">
                            <a href="order-detail.php?id=<?= $order['order_id'] ?>" class="order-btn view-btn">
                                View Details
                            </a>
                            
                            <?php if (in_array($order['order_status'], ['processing', 'pending'])): ?>
                                <a href="orders.php?cancel=<?= $order['order_id'] ?>" class="order-btn cancel-btn" onclick="return confirm('Are you sure you want to cancel this order?');">
                                    Cancel Order
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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