<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAdmin();

// Get statistics
$result = $connection->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $result->fetch_assoc()['total'];

$result = $connection->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
$total_customers = $result->fetch_assoc()['total'];

$result = $connection->query("SELECT SUM(total) as revenue FROM orders");
$total_revenue = $result->fetch_assoc()['revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <style>
        .admin-panel { padding: 20px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0; color: #666; }
        .stat-card .number { font-size: 28px; font-weight: bold; color: #2196F3; }
        .admin-menu { margin-bottom: 30px; }
        .admin-menu a { display: inline-block; padding: 10px 20px; margin-right: 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 4px; }
        .admin-menu a:hover { background: #1976D2; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-nav">
            <a href="dashboard.php" class="logo">🛒 Online Supermarket - Admin</a>
            <ul class="menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="orders.php">Manage Orders</a></li>
                <li><a href="products.php">Manage Products</a></li>
                <li><span><?php echo $_SESSION['full_name']; ?></span></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="admin-panel">
        <h2>Welcome, <?php echo $_SESSION['full_name']; ?></h2>
        
        <div class="stats">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?php echo $total_orders; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="number"><?php echo $total_customers; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="number">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>

        <div class="admin-menu">
            <a href="orders.php">Manage Orders</a>
            <a href="products.php">Manage Products</a>
            <a href="../index.php">Back to Store</a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Online Supermarket. Admin Panel.</p>
    </footer>
</body>
</html>
