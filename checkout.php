<?php
session_start();
include 'conn/conn.php';
include 'includes/session.php';
include 'includes/product-functions.php';
include 'includes/cart-functions.php';
include 'includes/favorites-functions.php';
include 'includes/order-functions.php';

requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$cartItems = getCartItems($userId);
$cartTotal = getCartTotal($userId);
$cartCount = getCartItemsCount($userId);
$favoritesCount = getFavoritesCount($userId);
$categories = getAllCategories();

// Redirect to cart if empty
if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

// Handle order creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    // Simple validation
    $errors = array();
    
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    
    if (empty($state)) {
        $errors[] = 'State is required';
    }
    
    if (empty($zip)) {
        $errors[] = 'ZIP code is required';
    }
    
    if (empty($paymentMethod)) {
        $errors[] = 'Payment method is required';
    }
    
    if (empty($errors)) {
        // Format shipping address
        $shippingAddress = json_encode([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zip' => $zip
        ]);
        
        // Create order
        $result = createOrder($userId, $shippingAddress, $paymentMethod);
        
        if ($result['success']) {
            header('Location: order-confirmation.php?order_id=' . $result['order_id']);
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Dripforge</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .checkout-page {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .page-title {
            width: 100%;
            font-size: 2.5rem;
            margin-bottom: 30px;
            color: #333;
            text-align: center;
        }
        
        .checkout-form {
            flex: 1;
            min-width: 500px;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .payment-methods {
            margin-top: 20px;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .payment-method:hover {
            background-color: #f9f9f9;
        }
        
        .payment-method.active {
            border-color: #007bff;
            background-color: #f0f7ff;
        }
        
        .payment-method input[type="radio"] {
            margin-right: 15px;
        }
        
        .payment-method-label {
            font-weight: 500;
            color: #333;
        }
        
        .upi-fields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        
        .upi-fields.active {
            display: block;
        }
        
        .order-summary {
            flex: 0 0 400px;
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 5px;
            align-self: flex-start;
        }
        
        .order-summary h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .item-name {
            flex: 1;
        }
        
        .item-quantity {
            color: #888;
            margin: 0 10px;
        }
        
        .item-total {
            font-weight: 500;
        }
        
        .order-totals {
            margin-top: 20px;
        }
        
        .order-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
        }
        
        .order-total {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .place-order-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px;
            width: 100%;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .place-order-btn:hover {
            background-color: #0056b3;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .error-message ul {
            margin: 10px 0 0 20px;
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

    <div class="checkout-page">
        <h1 class="page-title">Checkout</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Please correct the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="checkout-form">
            <form action="checkout.php" method="post">
                <div class="form-section">
                    <h3>Shipping Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= $user['first_name'] . ' ' . $user['last_name'] ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= $user['contact_number'] ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="zip">ZIP Code</label>
                            <input type="text" id="zip" name="zip" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Payment Method</h3>
                    
                    <div class="payment-methods">
                        <div class="payment-method" onclick="selectPaymentMethod('upi')">
                            <input type="radio" id="upi" name="payment_method" value="upi">
                            <label for="upi" class="payment-method-label">UPI Payment</label>
                        </div>
                        
                        <div class="upi-fields" id="upi-fields">
                            <div class="form-group">
                                <label for="upi_id">UPI ID</label>
                                <input type="text" id="upi_id" name="upi_id" class="form-control" placeholder="yourname@upi">
                                <small>* Payment will be processed after order confirmation</small>
                            </div>
                        </div>
                        
                        <div class="payment-method" onclick="selectPaymentMethod('cod')">
                            <input type="radio" id="cod" name="payment_method" value="cod">
                            <label for="cod" class="payment-method-label">Cash on Delivery</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="place-order-btn">Place Order</button>
            </form>
        </div>
        
        <div class="order-summary">
            <h3>Order Summary</h3>
            
            <div class="order-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <span class="item-name"><?= $item['product_name'] ?></span>
                        <span class="item-quantity">x<?= $item['quantity'] ?></span>
                        <span class="item-total">$<?= number_format($item['total_price'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-totals">
                <div class="order-totals-row">
                    <span>Subtotal</span>
                    <span>$<?= number_format($cartTotal, 2) ?></span>
                </div>
                
                <div class="order-totals-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                
                <div class="order-totals-row order-total">
                    <span>Total</span>
                    <span>$<?= number_format($cartTotal, 2) ?></span>
                </div>
            </div>
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

    <script>
        function selectPaymentMethod(method) {
            // Remove active class from all methods
            const methods = document.querySelectorAll('.payment-method');
            methods.forEach(el => el.classList.remove('active'));
            
            // Hide all method fields
            const upiFields = document.getElementById('upi-fields');
            upiFields.classList.remove('active');
            
            // Add active class to selected method
            document.querySelector(`#${method}`).checked = true;
            document.querySelector(`label[for="${method}"]`).parentElement.classList.add('active');
            
            // Show fields for selected method
            if (method === 'upi') {
                upiFields.classList.add('active');
            }
        }
    </script>
</body>
</html>