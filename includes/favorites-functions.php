<?php
include_once('session.php');

// Function to get favorites count for a user
function getFavoritesCount($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total_favorites FROM `favorites` WHERE `user_id` = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_favorites'] ?? 0;
}

// Function to get all favorites for a user
function getFavorites($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT f.favorite_id, p.* 
        FROM `favorites` f
        JOIN `products` p ON f.product_id = p.product_id
        WHERE f.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add product to favorites
function addToFavorites($userId, $productId) {
    global $conn;
    
    try {
        // Check if product already in favorites
        $stmt = $conn->prepare("SELECT * FROM `favorites` WHERE `user_id` = :user_id AND `product_id` = :product_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Already in favorites
            return true;
        }
        
        // Add to favorites
        $insertStmt = $conn->prepare("INSERT INTO `favorites` (`user_id`, `product_id`) VALUES (:user_id, :product_id)");
        $insertStmt->bindParam(':user_id', $userId);
        $insertStmt->bindParam(':product_id', $productId);
        $insertStmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error adding to favorites: " . $e->getMessage());
        return false;
    }
}

// Function to remove from favorites
function removeFromFavorites($favoriteId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM `favorites` WHERE `favorite_id` = :favorite_id");
        $stmt->bindParam(':favorite_id', $favoriteId);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error removing from favorites: " . $e->getMessage());
        return false;
    }
}

// Function to check if product is in favorites
function isInFavorites($userId, $productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM `favorites` WHERE `user_id` = :user_id AND `product_id` = :product_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}
?>