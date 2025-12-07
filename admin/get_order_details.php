<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkLogin();

if (isset($_GET['id'])) {
    $order_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify the order belongs to the user
    $query = "SELECT * FROM order_details WHERE order_id = ? 
              AND order_id IN (SELECT id FROM orders WHERE user_id = ?)";
    $statement = $connection->prepare($query);
    $statement->bind_param("ii", $order_id, $user_id);
    $statement->execute();
    $order_details = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (!empty($order_details)) {
        ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Product</th>
                    <th style="padding: 10px; text-align: center; border-bottom: 1px solid #ddd;">Quantity</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Unit Price</th>
                    <th style="padding: 10px; text-align: right; border-bottom: 1px solid #ddd;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total = 0;
                foreach ($order_details as $detail):
                    $product = getProductById($connection, $detail['product_id']);
                    $subtotal = $detail['unit_price'] * $detail['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($product['name']); ?></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;"><?php echo $detail['quantity']; ?></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$<?php echo number_format($detail['unit_price'], 2); ?></td>
                    <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">$<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div style="text-align: right; margin-top: 15px; font-weight: bold;">
            <p>Total: $<?php echo number_format($total, 2); ?></p>
        </div>
        <?php
    }
}
?>
