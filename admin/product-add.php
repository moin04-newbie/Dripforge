<?php
session_start();
include '../conn/conn.php';
include '../includes/session.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productName = $_POST['product_name'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $description = $_POST['description'] ?? '';
    $imageUrl = $_POST['image_url'] ?? '';
    
    // Validate data
    if (empty($productName) || empty($categoryId) || empty($price) || !isset($stock) || empty($description) || empty($imageUrl)) {
        header('Location: products.php?error=1');
        exit();
    }
    
    try {
        // Get category name based on category ID
        $stmtCategory = $conn->prepare("SELECT category_name FROM categories WHERE category_id = :category_id");
        $stmtCategory->bindParam(':category_id', $categoryId);
        $stmtCategory->execute();
        $category = $stmtCategory->fetch(PDO::FETCH_ASSOC);
        $categoryName = $category['category_name'];
        
        // Insert product
        $stmt = $conn->prepare("
            INSERT INTO products (category_id, category_name, product_name, price, description, stock, image_url) 
            VALUES (:category_id, :category_name, :product_name, :price, :description, :stock, :image_url)
        ");
        
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->bindParam(':category_name', $categoryName);
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image_url', $imageUrl);
        
        $stmt->execute();
        
        // Redirect back with success message
        header('Location: products.php?added=1');
        exit();
    } catch (PDOException $e) {
        // Redirect back with error
        header('Location: products.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Not a POST request, redirect back
    header('Location: products.php');
    exit();
}
?>