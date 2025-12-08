<?php
checkLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: index.php?action=orders');
    exit;
}

// Get order details
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$statement = $connection->prepare($query);
$statement->bind_param("ii", $order_id, $_SESSION['user_id']);
$statement->execute();
$order = $statement->get_result()->fetch_assoc();

if (!$order) {
    header('Location: index.php?action=orders');
    exit;
}
?>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase</p>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="label">Order ID:</span>
                <span class="value">#<?php echo $order_id; ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Order Date:</span>
                <span class="value"><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value">$<?php echo number_format($order['total'], 2); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value status-badge"><?php echo ucfirst($order['status']); ?></span>
            </div>
        </div>
        
        <div class="success-actions">
            <a href="index.php?action=orders" class="btn btn-primary">View My Orders</a>
            <a href="index.php?action=store" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<style>
.success-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 500px;
    padding: 20px;
}

.success-card {
    background: white;
    padding: 60px 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    max-width: 500px;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #27ae60;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    margin: 0 auto 20px;
}

.success-card h2 {
    color: #2c3e50;
    font-size: 28px;
    margin-bottom: 10px;
}

.success-card p {
    color: #7f8c8d;
    margin-bottom: 30px;
}

.order-details {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin: 30px 0;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0f1;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row .label {
    font-weight: 600;
    color: #7f8c8d;
}

.detail-row .value {
    color: #2c3e50;
    font-weight: 600;
}

.status-badge {
    background: #d4edda;
    color: #155724;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
}

.success-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    flex-direction: column;
}

.success-actions .btn {
    padding: 12px 24px;
}
</style>
