<?php
function getAllCategories() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM `categories` ORDER BY `category_name`");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get category by ID
function getCategoryById($categoryId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM `categories` WHERE `category_id` = :category_id");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to get all products
function getAllProducts($limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM `products` ORDER BY `created_at` DESC";
    
    if ($limit) {
        $sql .= " LIMIT :limit";
    }
    
    $stmt = $conn->prepare($sql);
    
    if ($limit) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get products by category
function getProductsByCategory($categoryName) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM `products` WHERE `category_name` = :category_name ORDER BY `product_name`");
    $stmt->bindParam(':category_name', $categoryName);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get product by ID
function getProductById($productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM `products` WHERE `product_id` = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Function to search products
function searchProducts($query) {
    global $conn;
    
    $searchQuery = "%$query%";
    
    $stmt = $conn->prepare("
        SELECT * FROM `products` 
        WHERE `product_name` LIKE :query 
        OR `description` LIKE :query 
        ORDER BY `product_name`
    ");
    $stmt->bindParam(':query', $searchQuery);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get related products
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM `products` 
        WHERE `category_id` = :category_id 
        AND `product_id` != :product_id 
        ORDER BY RAND() 
        LIMIT :limit
    ");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->bindParam(':product_id', $productId);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get featured products
function getFeaturedProducts($limit = 3) {
    global $conn;
    
    // In a real scenario, you might have a "featured" flag in your products table
    // For now, we'll just get random products
    $stmt = $conn->prepare("
        SELECT * FROM `products` 
        ORDER BY RAND() 
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>