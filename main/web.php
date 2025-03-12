<?php
session_start();
include '../conn/conn.php';
include '../includes/session.php';
include '../includes/product-functions.php';
include '../includes/cart-functions.php';
include '../includes/favorites-functions.php';

$featuredProducts = getFeaturedProducts(3);
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
    <title>Dripforge.com</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
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
        <li><a href="web.php">Home</a></li>
        <li><a href="../shop.php">Shop</a></li>
        <li><a href="../about.php">About Us</a></li>
        <li><a href="../contact.php">Contact Us</a></li>
        </ul>
    </nav>

    <nav class="navbar2">
        <ul class="nav-links">
            <?php foreach ($categories as $category): ?>
                <li><a href="../categories/<?= strtolower($category['category_name']) ?>.php"><?= ucfirst($category['category_name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Icons Container -->
    <div class="icon-container">
        <!-- Cart Icon -->
        <a href="../cart.php" class="icon-badge">
            <i class="fas fa-shopping-cart"></i>
            <?php if ($cartCount > 0): ?>
                <span class="badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        
        <!-- Favorites Icon -->
        <a href="../favorites.php" class="icon-badge">
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
                    <a href="../profile.php">My Profile</a>
                    <a href="../orders.php">My Orders</a>
                    <a href="../logout.php">Logout</a>
                </div>
            <?php else: ?>
                <a href="../index.php" class="dropdown-toggle">
                    <i class="fas fa-user"></i>
                    Login/Register
                </a>
            <?php endif; ?>
        </div>
    </div>
 
    <div class="slider">
        <h1 class="main-heading">Dripforge</h1>
        <div class="slides">
          <!-- Slide 1 -->
          <div class="slide">
            <img src="../images/slider3.jpg" alt="Image 1">
            <div class="slide-text">Exclusive Collections!</div>
          </div>
          <!-- Slide 2 -->
          <div class="slide">
            <img src="../images/slider2.jpg" alt="Image 2">
            <div class="slide-text">Latest Drops Available Now!</div>
          </div>
          <!-- Slide 3 -->
          <div class="slide">
            <img src="../images/slider1.jpg" alt="Image 3">
            <div class="slide-text">Premium Streetwear!</div>
          </div>
        </div>
        <button class="prev" onclick="prevSlide()">&#10094;</button>
        <button class="next" onclick="nextSlide()">&#10095;</button>
      </div>


   <main class="main-content">
     <section class="hero-section">
        <div class="hero-content">
         <h2>Forge Your Style</h2>
         <p>Discover the latest in streetwear fashion</p>
       </div>
     </section>
   </main>
       
        
        <section class="featured-products" style="margin-top: 2rem;"> 
            <h2>Featured Collections</h2>
            <div class="product-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($category['category_name'] == 'fashion'): ?>
                                <img src="../assets/streetwear.jpg" alt="<?= $category['category_name'] ?> Collection">
                            <?php elseif ($category['category_name'] == 'sneakers'): ?>
                                <img src="../assets/sneaker.jpg" alt="<?= $category['category_name'] ?> Collection">
                            <?php else: ?>
                                <img src="../assets/drip.jpg" alt="<?= $category['category_name'] ?> Collection">
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= ucfirst($category['category_name']) ?> Collection</h3>
                            <p><?= $category['description'] ?></p>
                            <span class="price">From $9.99</span>
                            <a href="../categories/<?= strtolower($category['category_name']) ?>.php" class="shop-now-btn">Shop Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="about-section">
            <div class="about-content">
                <h2>About Dripforge</h2>
                <p>We are more than just a fashion brand - we're a movement. At Dripforge, we blend street culture with
                    high fashion to create unique pieces that make you stand out.</p>
                <a href="about.php" class="shop-now-btn" style="display:inline-block; width:auto; padding:10px 20px; margin-top:20px;">Learn More</a>
            </div>
        </section>

        <section class="newsletter">
            <div class="newsletter-content">
                <h2>Stay Updated</h2>
                <p>Subscribe to get exclusive deals and early access to new drops</p>
                <form class="newsletter-form">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </section>
    </main>

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

    <script src="../script.js"></script>

</body>
</html>