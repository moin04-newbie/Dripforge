<?php
header("Content-Type: application/json");
include ('../conn/conn.php');
// one sec 
try {
    $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_name IN ('sneakers', 'fashion', 'accessories') 
        GROUP BY category_name 
        ORDER BY product_id ASC
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Category mapping for redirection
    $categoryRedirects = [
        "fashion" => "page1.html",
        "sneakers" => "page2.html",
        "accessories" => "page3.html"
    ];

    // Add redirect URLs to products
    foreach ($products as &$product) {
        if (isset($categoryRedirects[$product['category_name']])) {
            $product['redirect_url'] = $categoryRedirects[$product['category_name']];
        }
    }

    echo json_encode(["status" => "success", "products" => $products]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
