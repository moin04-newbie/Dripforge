<?php
include_once('session.php');
include_once('cart-functions.php');

function createOrder($userId, $shippingAddress, $paymentMethod) {
    global $conn;
    
    try {
        // Get cart items
        $cartItems = getCartItems($userId);
        
        if (empty($cartItems)) {
            return ['success' => false, 'message' => 'Your cart is empty'];
        }
        
        // Calculate total amount
        $totalAmount = getCartTotal($userId);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Create order
        $stmt = $conn->prepare("
            INSERT INTO `orders` (`user_id`, `total_amount`, `shipping_address`, `payment_method`) 
            VALUES (:user_id, :total_amount, :shipping_address, :payment_method)
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':total_amount', $totalAmount);
        $stmt->bindParam(':shipping_address', $shippingAddress);
        $stmt->bindParam(':payment_method', $paymentMethod);
        $stmt->execute();
        
        $orderId = $conn->lastInsertId();
        
        // Add order items
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("
                INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `price`) 
                VALUES (:order_id, :product_id, :quantity, :price)
            ");
            $stmt->bindParam(':order_id', $orderId);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $stmt->execute();
            
            // Update product stock
            $newStock = $item['stock'] - $item['quantity'];
            $stmt = $conn->prepare("UPDATE `products` SET `stock` = :stock WHERE `product_id` = :product_id");
            $stmt->bindParam(':stock', $newStock);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }
        
        // Clear the cart
        clearCart($userId);
        
        // Commit transaction
        $conn->commit();
        
        return ['success' => true, 'order_id' => $orderId];
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();
        
        // Log error
        error_log("Error creating order: " . $e->getMessage());
        
        return ['success' => false, 'message' => 'Failed to create order'];
    }
}

// Function to get order details
function getOrderById($orderId, $userId = null) {
    global $conn;
    
    try {
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email, u.contact_number  
            FROM `orders` o
            JOIN `tbl_user` u ON o.user_id = u.tbl_user_id
            WHERE o.order_id = :order_id
        ";
        
        // If user ID is provided, only allow retrieving their own orders
        if ($userId) {
            $sql .= " AND o.user_id = :user_id";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId);
        
        if ($userId) {
            $stmt->bindParam(':user_id', $userId);
        }
        
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return null;
        }
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.product_name, p.image_url 
            FROM `order_items` oi
            JOIN `products` p ON oi.product_id = p.product_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $order;
    } catch (PDOException $e) {
        // Log error
        error_log("Error getting order: " . $e->getMessage());
        return null;
    }
}

// Function to get user orders
function getUserOrders($userId) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM `orders` 
            WHERE `user_id` = :user_id 
            ORDER BY `created_at` DESC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Error getting user orders: " . $e->getMessage());
        return [];
    }
}

// Function to cancel order
function cancelOrder($orderId, $userId) {
    global $conn;
    
    try {
        // Check if order belongs to user and can be cancelled
        $stmt = $conn->prepare("
            SELECT * FROM `orders` 
            WHERE `order_id` = :order_id 
            AND `user_id` = :user_id 
            AND `order_status` IN ('processing', 'pending')
        ");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Order cannot be cancelled'];
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update order status
        $stmt = $conn->prepare("
            UPDATE `orders` 
            SET `order_status` = 'cancelled' 
            WHERE `order_id` = :order_id
        ");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        // Get order items to restore stock
        $stmt = $conn->prepare("
            SELECT oi.product_id, oi.quantity 
            FROM `order_items` oi
            WHERE oi.order_id = :order_id
        ");
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore product stock
        foreach ($items as $item) {
            $stmt = $conn->prepare("
                UPDATE `products` 
                SET `stock` = `stock` + :quantity 
                WHERE `product_id` = :product_id
            ");
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        return ['success' => true];
    } catch (PDOException $e) {
        // Rollback transaction
        $conn->rollBack();
        
        // Log error
        error_log("Error cancelling order: " . $e->getMessage());
        
        return ['success' => false, 'message' => 'Failed to cancel order'];
    }
}

// Function to update order status (admin)
function updateOrderStatus($orderId, $status) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE `orders` 
            SET `order_status` = :status 
            WHERE `order_id` = :order_id
        ");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $orderId);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log error
        error_log("Error updating order status: " . $e->getMessage());
        return false;
    }
}

// Function to get all orders (admin)
function getAllOrders() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM `orders` o
            JOIN `tbl_user` u ON o.user_id = u.tbl_user_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error
        error_log("Error getting all orders: " . $e->getMessage());
        return [];
    }
}
?>