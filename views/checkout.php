<?php
checkLogin();

$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (empty($cart)) {
    header('Location: index.php?action=cart');
    exit;
}

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$statement = $connection->prepare($query);
$statement->bind_param("i", $user_id);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();

$total = 0;
$cart_items = [];

foreach ($cart as $product_id => $quantity) {
    $product = getProductById($connection, $product_id);
    $subtotal = $product['price'] * $quantity;
    $total += $subtotal;
    $cart_items[] = [
        'product' => $product,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

// Process order confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $delivery_address = sanitize($_POST['delivery_address']);
    
    if (empty($delivery_address)) {
        $error = 'Please enter a delivery address';
    } else {
        // Create order with confirmed address
        $query = "INSERT INTO orders (user_id, total, shipping_address) VALUES (?, ?, ?)";
        $statement = $connection->prepare($query);
        $statement->bind_param("ids", $user_id, $total, $delivery_address);
        
        if ($statement->execute()) {
            $order_id = $connection->insert_id;
            
            // Store order with delivery address (can be added as order_address field)
            foreach ($cart_items as $item) {
                $query = "INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)";
                $stmt = $connection->prepare($query);
                $unit_price = $item['product']['price'];
                $product_id = $item['product']['id'];
                $qty = $item['quantity'];
                $stmt->bind_param("iiid", $order_id, $product_id, $qty, $unit_price);
                $stmt->execute();
                
                // Update stock
                updateStock($connection, $product_id, $qty);
            }
            
            unset($_SESSION['cart']);
            header('Location: index.php?action=order_success&id=' . $order_id);
            exit;
        }
    }
}
?>

<div class="checkout-container">
    <div class="checkout-header">
        <h2>🛍️ Order Confirmation</h2>
        <p>Review your order before completing the purchase</p>
    </div>
    
    <div class="checkout-grid">
        <!-- Order Summary -->
        <div class="checkout-section">
            <h3>📦 Order Summary</h3>
            <div class="order-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div class="item-info">
                            <strong><?php echo htmlspecialchars($item['product']['name']); ?></strong>
                            <small>Qty: <?php echo $item['quantity']; ?></small>
                        </div>
                        <div class="item-price">
                            $<?php echo number_format($item['subtotal'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="order-total">
                <strong>Total:</strong>
                <strong class="total-amount">$<?php echo number_format($total, 2); ?></strong>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="checkout-section">
            <h3>📍 Delivery Information</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Delivery Address:</label>
                    <textarea name="delivery_address" rows="4" required placeholder="Enter your complete delivery address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    <small>This address will be used for this order</small>
                </div>
                
                <div class="user-info">
                    <div class="info-row">
                        <span class="label">Name:</span>
                        <span class="value"><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone:</span>
                        <span class="value"><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                </div>
                
                <div class="checkout-actions">
                    <button type="submit" name="confirm_order" class="btn btn-primary btn-large">✓ Confirm & Place Order</button>
                    <a href="index.php?action=cart" class="btn btn-secondary">← Back to Cart</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.checkout-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-header {
    text-align: center;
    margin-bottom: 40px;
}

.checkout-header h2 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

.checkout-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
}

.checkout-section h3 {
    color: #2c3e50;
    margin-bottom: 20px;
}

.order-items {
    margin-bottom: 20px;
    max-height: 300px;
    overflow-y: auto;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: white;
    border-radius: 6px;
    margin-bottom: 10px;
    border-left: 4px solid #3498db;
}

.item-info strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 5px;
}

.item-info small {
    color: #7f8c8d;
    font-size: 12px;
}

.item-price {
    font-size: 18px;
    font-weight: 700;
    color: #27ae60;
}

.order-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border-top: 2px solid #ecf0f1;
    font-size: 18px;
}

.total-amount {
    color: #27ae60;
}

.user-info {
    background: white;
    padding: 15px;
    border-radius: 6px;
    margin: 20px 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-row .label {
    font-weight: 600;
    color: #7f8c8d;
}

.info-row .value {
    color: #2c3e50;
}

.checkout-actions {
    display: flex;
    gap: 15px;
    margin-top: 25px;
}

.btn-large {
    flex: 1;
    padding: 15px;
    font-size: 16px;
}

@media (max-width: 768px) {
    .checkout-grid {
        grid-template-columns: 1fr;
    }
    
    .checkout-actions {
        flex-direction: column;
    }
}
</style>
