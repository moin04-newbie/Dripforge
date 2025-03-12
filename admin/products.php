<?php
session_start();
include '../conn/conn.php';
include '../includes/session.php';
include '../includes/product-functions.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get all products
$products = getAllProducts();
$categories = getAllCategories();

// Handle product deletion
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $productId = $_GET['delete'];
    
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :product_id");
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        
        // Redirect to refresh page
        header('Location: products.php?deleted=1');
        exit();
    } catch (PDOException $e) {
        $error = "Error deleting product: " . $e->getMessage();
    }
}

// Success/error messages
$message = '';
if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $message = 'Product deleted successfully';
    $messageType = 'success';
}
if (isset($_GET['added']) && $_GET['added'] == 1) {
    $message = 'Product added successfully';
    $messageType = 'success';
}
if (isset($_GET['updated']) && $_GET['updated'] == 1) {
    $message = 'Product updated successfully';
    $messageType = 'success';
}
if (isset($error)) {
    $message = $error;
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Dripforge Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
            z-index: 1;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #495057;
            text-align: center;
        }
        
        .sidebar-header h3 {
            color: white;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            color: #adb5bd;
            margin-bottom: 0;
        }
        
        .nav-item {
            width: 100%;
        }
        
        .nav-link {
            color: #ced4da;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white;
            background-color: #495057;
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #eee;
            font-weight: 600;
            color: #495057;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .btn-group-action .btn {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
            margin-right: 5px;
        }
        
        .logout-link {
            margin-top: auto;
            padding: 15px 20px;
            color: #dc3545;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .logout-link:hover {
            color: white;
            background-color: #dc3545;
            text-decoration: none;
        }
        
        .logout-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Modal styles */
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Dripforge</h3>
            <p>Admin Dashboard</p>
        </div>
        
        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="products.php">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categories.php">
                    <i class="fas fa-tags"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
        </ul>
        
        <a href="logout.php" class="logout-link mt-auto">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
    
    <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <h1>Manage Products</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <span>All Products</span>
                <form class="form-inline">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-sm mr-2" placeholder="Search products...">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="60">Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">No products found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="product-image">
                                            <img src="../<?= $product['image_url'] ?>" alt="<?= $product['product_name'] ?>">
                                        </div>
                                    </td>
                                    <td><?= $product['product_name'] ?></td>
                                    <td><?= ucfirst($product['category_name']) ?></td>
                                    <td>$<?= number_format($product['price'], 2) ?></td>
                                    <td><?= $product['stock'] ?></td>
                                    <td>
                                        <div class="btn-group-action">
                                            <button class="btn btn-sm btn-outline-primary edit-product" 
                                                data-id="<?= $product['product_id'] ?>"
                                                data-name="<?= $product['product_name'] ?>"
                                                data-category="<?= $product['category_id'] ?>"
                                                data-price="<?= $product['price'] ?>"
                                                data-stock="<?= $product['stock'] ?>"
                                                data-description="<?= htmlspecialchars($product['description']) ?>"
                                                data-image="<?= $product['image_url'] ?>"
                                                data-toggle="modal" 
                                                data-target="#editProductModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="products.php?delete=<?= $product['product_id'] ?>" class="btn btn-sm btn-outline-danger delete-product" onclick="return confirm('Are you sure you want to delete this product?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="product-add.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="productName">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="product_name" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="categoryId">Category</label>
                                <select class="form-control" id="categoryId" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['category_id'] ?>"><?= ucfirst($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="productPrice">Price ($)</label>
                                <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="productStock">Stock</label>
                            <input type="number" class="form-control" id="productStock" name="stock" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="productDescription">Description</label>
                            <textarea class="form-control" id="productDescription" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="productImage">Image URL</label>
                            <input type="text" class="form-control" id="productImage" name="image_url" placeholder="assets/image.jpg" required>
                            <small class="form-text text-muted">Enter the path to the image (e.g., assets/image.jpg)</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Image Upload (Coming Soon)</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" disabled>
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                            <small class="form-text text-muted">Cloudinary integration coming soon. For now, use existing image paths.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="product-update.php" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="editProductId" name="product_id">
                        
                        <div class="form-group">
                            <label for="editProductName">Product Name</label>
                            <input type="text" class="form-control" id="editProductName" name="product_name" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="editCategoryId">Category</label>
                                <select class="form-control" id="editCategoryId" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['category_id'] ?>"><?= ucfirst($category['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="editProductPrice">Price ($)</label>
                                <input type="number" class="form-control" id="editProductPrice" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="editProductStock">Stock</label>
                            <input type="number" class="form-control" id="editProductStock" name="stock" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="editProductDescription">Description</label>
                            <textarea class="form-control" id="editProductDescription" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="editProductImage">Image URL</label>
                            <input type="text" class="form-control" id="editProductImage" name="image_url" placeholder="assets/image.jpg" required>
                            <small class="form-text text-muted">Enter the path to the image (e.g., assets/image.jpg)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <script>
        // Handle edit product
        $('.edit-product').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const category = $(this).data('category');
            const price = $(this).data('price');
            const stock = $(this).data('stock');
            const description = $(this).data('description');
            const image = $(this).data('image');
            
            $('#editProductId').val(id);
            $('#editProductName').val(name);
            $('#editCategoryId').val(category);
            $('#editProductPrice').val(price);
            $('#editProductStock').val(stock);
            $('#editProductDescription').val(description);
            $('#editProductImage').val(image);
        });
    </script>
</body>
</html>