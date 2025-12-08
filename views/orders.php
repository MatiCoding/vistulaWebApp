<?php
checkLogin();

$user_id = $_SESSION['user_id'];
$view_order = isset($_GET['view']) ? (int)$_GET['view'] : null;

// Get orders
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$statement = $connection->prepare($query);
$statement->bind_param("i", $user_id);
$statement->execute();
$orders = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

// Get single order details
$order_details = null;
$order_items = [];
if ($view_order) {
    $query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("ii", $view_order, $user_id);
    $statement->execute();
    $order_details = $statement->get_result()->fetch_assoc();
    
    if ($order_details) {
        $query = "SELECT od.*, p.name FROM order_details od 
                  JOIN products p ON od.product_id = p.id 
                  WHERE od.order_id = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("i", $view_order);
        $statement->execute();
        $order_items = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div class="orders-container">
    <?php if ($view_order && $order_details): ?>
        <!-- ORDER DETAIL VIEW -->
        <a href="index.php?action=orders" class="btn btn-secondary" style="margin-bottom: 20px;">← Back to Orders</a>
        
        <div class="order-detail">
            <h2>📦 Order #<?php echo $order_details['id']; ?></h2>
            
            <div class="order-info-grid">
                <div class="info-card">
                    <h3>Order Information</h3>
                    <div class="info-item">
                        <span class="label">Order ID:</span>
                        <span class="value">#<?php echo $order_details['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Order Date:</span>
                        <span class="value"><?php echo date('M d, Y H:i', strtotime($order_details['order_date'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Status:</span>
                        <span class="value status-badge"><?php echo ucfirst($order_details['status']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">Total Amount:</span>
                        <span class="value" style="color: #27ae60; font-weight: 700; font-size: 18px;">$<?php echo number_format($order_details['total'], 2); ?></span>
                    </div>
                </div>
                
                <div class="info-card">
                    <h3>📍 Delivery Address</h3>
                    <div class="address-box">
                        <?php
                        // Intenta traer dirección del campo order_address si existe
                        // Si no, usa la dirección actual del usuario
                        $query = "SELECT shipping_address FROM orders WHERE id = ?";
                        $statement = $connection->prepare($query);
                        $statement->bind_param("i", $order_details['id']);
                        $statement->execute();
                        $user = $statement->get_result()->fetch_assoc();
                        ?>
                        <p><?php echo nl2br(htmlspecialchars($user['shipping_address'] ?? 'Address not provided')); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="order-items">
                <h3>Order Items</h3>
                <table class="table-order">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="public/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 50px; max-height: 50px; border-radius: 4px;">
                                    <?php else: ?>
                                        <span style="color: #999;">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                <td>$<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- ORDERS LIST VIEW -->
        <h2>📦 My Orders</h2>
        
        <?php if (empty($orders)): ?>
            <div class="no-orders">
                <p>You haven't placed any orders yet</p>
                <a href="index.php?action=store" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-id">
                                <strong>Order #<?php echo $order['id']; ?></strong>
                                <small><?php echo date('M d, Y', strtotime($order['order_date'])); ?></small>
                            </div>
                            <div class="order-status">
                                <span class="status-badge"><?php echo ucfirst($order['status']); ?></span>
                            </div>
                        </div>
                        
                        <div class="order-body">
                            <div class="order-col">
                                <span class="label">Total:</span>
                                <span class="value">$<?php echo number_format($order['total'], 2); ?></span>
                            </div>
                            <div class="order-col">
                                <span class="label">Items:</span>
                                <span class="value"><?php 
                                    $item_count_query = "SELECT COUNT(*) as count FROM order_details WHERE order_id = ?";
                                    $stmt = $connection->prepare($item_count_query);
                                    $stmt->bind_param("i", $order['id']);
                                    $stmt->execute();
                                    $count_result = $stmt->get_result()->fetch_assoc();
                                    echo $count_result['count'];
                                ?></span>
                            </div>
                        </div>
                        
                        <div class="order-footer">
                            <a href="index.php?action=orders&view=<?php echo $order['id']; ?>" class="btn btn-primary btn-small">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.orders-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.no-orders {
    text-align: center;
    padding: 60px 20px;
}

.no-orders p {
    color: #7f8c8d;
    margin-bottom: 20px;
}

/* Orders List */
.orders-list {
    display: grid;
    gap: 20px;
}

.order-card {
    border: 1px solid #ecf0f1;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: #3498db;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #ecf0f1;
}

.order-id strong {
    display: block;
    color: #2c3e50;
    font-size: 16px;
    margin-bottom: 5px;
}

.order-id small {
    color: #7f8c8d;
    font-size: 13px;
}

.order-status {
    text-align: right;
}

.status-badge {
    display: inline-block;
    background: #d4edda;
    color: #155724;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.order-body {
    padding: 20px;
    display: flex;
    gap: 40px;
}

.order-col {
    flex: 1;
}

.order-col .label {
    display: block;
    color: #7f8c8d;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.order-col .value {
    display: block;
    color: #2c3e50;
    font-size: 18px;
    font-weight: 700;
}

.order-footer {
    padding: 15px 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    gap: 10px;
}

/* Order Detail */
.order-detail h2 {
    color: #2c3e50;
    margin-bottom: 30px;
}

.order-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.info-card {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #ecf0f1;
}

.info-card h3 {
    margin-top: 0;
    color: #2c3e50;
    font-size: 16px;
    margin-bottom: 15px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-item .label {
    color: #7f8c8d;
    font-weight: 600;
}

.info-item .value {
    color: #2c3e50;
    font-weight: 600;
}

.address-box {
    background: white;
    padding: 15px;
    border-radius: 6px;
    line-height: 1.6;
    color: #2c3e50;
    font-size: 14px;
}

.order-items {
    margin-top: 40px;
}

.order-items h3 {
    color: #2c3e50;
    margin-bottom: 20px;
}

.table-order {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 1px solid #ecf0f1;
    border-radius: 8px;
    overflow: hidden;
}

.table-order thead {
    background: #f8f9fa;
}

.table-order th {
    padding: 15px;
    text-align: left;
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 1px solid #ecf0f1;
}

.table-order td {
    padding: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.table-order tbody tr:hover {
    background: #f8f9fa;
}

@media (max-width: 768px) {
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .order-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .order-body {
        flex-direction: column;
        gap: 15px;
    }
    
    .order-status {
        margin-top: 15px;
        text-align: left;
    }
    
    .table-order {
        font-size: 13px;
    }
    
    .table-order th,
    .table-order td {
        padding: 10px;
    }
}
</style>
