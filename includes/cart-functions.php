<?php
include_once('session.php');

// Function to get cart items count for a user
function getCartItemsCount($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM `cart` WHERE `user_id` = :user_id");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_items'] ?? 0;
}

// Function to get all cart items for a user
function getCartItems($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT c.cart_id, c.quantity, p.*, c.quantity * p.price AS total_price 
        FROM `cart` c
        JOIN `products` p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add item to cart
function addToCart($userId, $productId, $quantity = 1) {
    global $conn;
    
    try {
        // Check if product exists in cart
        $stmt = $conn->prepare("SELECT * FROM `cart` WHERE `user_id` = :user_id AND `product_id` = :product_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Update quantity if product already in cart
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            $updateStmt = $conn->prepare("UPDATE `cart` SET `quantity` = :quantity WHERE `cart_id` = :cart_id");
            $updateStmt->bindParam(':quantity', $newQuantity);
            $updateStmt->bindParam(':cart_id', $cartItem['cart_id']);
            $updateStmt->execute();
        } else {
            // Insert new cart item
            $insertStmt = $conn->prepare("INSERT INTO `cart` (`user_id`, `product_id`, `quantity`) VALUES (:user_id, :product_id, :quantity)");
            $insertStmt->bindParam(':user_id', $userId);
            $insertStmt->bindParam(':product_id', $productId);
            $insertStmt->bindParam(':quantity', $quantity);
            $insertStmt->execute();
        }
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error adding to cart: " . $e->getMessage());
        return false;
    }
}

// Function to update cart item quantity
function updateCartItem($cartId, $quantity) {
    global $conn;
    
    try {
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or negative
            $stmt = $conn->prepare("DELETE FROM `cart` WHERE `cart_id` = :cart_id");
            $stmt->bindParam(':cart_id', $cartId);
        } else {
            // Update quantity
            $stmt = $conn->prepare("UPDATE `cart` SET `quantity` = :quantity WHERE `cart_id` = :cart_id");
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':cart_id', $cartId);
        }
        
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error updating cart: " . $e->getMessage());
        return false;
    }
}

// Function to remove item from cart
function removeFromCart($cartId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM `cart` WHERE `cart_id` = :cart_id");
        $stmt->bindParam(':cart_id', $cartId);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error removing from cart: " . $e->getMessage());
        return false;
    }
}

// Function to get cart total price
function getCartTotal($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT SUM(c.quantity * p.price) as total_price 
        FROM `cart` c
        JOIN `products` p ON c.product_id = p.product_id
        WHERE c.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total_price'] ?? 0;
}

// Function to clear cart after order
function clearCart($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM `cart` WHERE `user_id` = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error clearing cart: " . $e->getMessage());
        return false;
    }
}
?>