<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAdmin();

// Process status change
if (isset($_POST['change_status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = sanitize($_POST['status']);
    
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("si", $new_status, $order_id);
    $statement->execute();
    
    echo '<div class="alert alert-success">Order status updated</div>';
}

// Get all orders
$query = "SELECT o.*, u.full_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC";
$result = $connection->query($query);
$orders = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container-nav">
            <a href="dashboard.php" class="logo">🛒 Online Supermarket - Admin</a>
            <ul class="menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="orders.php">Manage Orders</a></li>
                <li><a href="products.php">Manage Products</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Order Management</h2>
        
        <table class="table-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Shipping Address</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                    <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                    <td><?php echo ucfirst($order['status']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="change_status" class="btn btn-primary btn-small">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Online Supermarket. Admin Panel.</p>
    </footer>
</body>
</html>
