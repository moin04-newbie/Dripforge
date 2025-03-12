<?php
session_start();
include '../conn/conn.php';
include '../includes/session.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Function to count total users
function countUsers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tbl_user");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Function to count total products
function countProducts($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Function to count total orders
function countOrders($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}

// Function to get recent orders
function getRecentOrders($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT o.*, u.first_name, u.last_name 
        FROM orders o
        JOIN tbl_user u ON o.user_id = u.tbl_user_id
        ORDER BY o.created_at DESC
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get dashboard data
$totalUsers = countUsers($conn);
$totalProducts = countProducts($conn);
$totalOrders = countOrders($conn);
$recentOrders = getRecentOrders($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Dripforge</title>
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
        }
        
        .stats-card {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            color: white;
        }
        
        .stats-card.blue {
            background-color: #007bff;
        }
        
        .stats-card.green {
            background-color: #28a745;
        }
        
        .stats-card.orange {
            background-color: #fd7e14;
        }
        
        .stats-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stats-card .count {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card .label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
        
        .badge {
            padding: 8px 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
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
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">
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
            <h1>Dashboard</h1>
            <div>
                <span class="text-muted">Welcome, </span>
                <span class="font-weight-bold"><?= $_SESSION['admin_username'] ?></span>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card blue">
                    <i class="fas fa-users"></i>
                    <div class="count"><?= $totalUsers ?></div>
                    <div class="label">Total Users</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card green">
                    <i class="fas fa-box"></i>
                    <div class="count"><?= $totalProducts ?></div>
                    <div class="label">Total Products</div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="stats-card orange">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="count"><?= $totalOrders ?></div>
                    <div class="label">Total Orders</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Recent Orders
                    </div>
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No orders found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['order_id'] ?></td>
                                            <td><?= $order['first_name'] . ' ' . $order['last_name'] ?></td>
                                            <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <?php
                                                    $statusClass = '';
                                                    switch ($order['order_status']) {
                                                        case 'processing':
                                                            $statusClass = 'badge-primary';
                                                            break;
                                                        case 'shipped':
                                                            $statusClass = 'badge-info';
                                                            break;
                                                        case 'delivered':
                                                            $statusClass = 'badge-success';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'badge-danger';
                                                            break;
                                                        default:
                                                            $statusClass = 'badge-secondary';
                                                    }
                                                ?>
                                                <span class="badge <?= $statusClass ?>"><?= ucfirst($order['order_status']) ?></span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="order-detail.php?id=<?= $order['order_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <a href="orders.php" class="btn btn-outline-primary btn-sm">View All Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
</body>
</html>