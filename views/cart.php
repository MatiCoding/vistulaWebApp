<?php
checkLogin();

$user_id = $_SESSION['user_id'];
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Process add to cart from home
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    $cart = $_SESSION['cart'];
}

// Remove from cart
if (isset($_GET['remove'])) {
    $product_id = (int) $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    $cart = $_SESSION['cart'];
}

// Update quantity
if (isset($_POST['update_quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$product_id]);
    }
    $cart = $_SESSION['cart'];
}

$total = 0;
$cart_items = [];

foreach ($cart as $product_id => $quantity) {
    $product = getProductById($connection, $product_id);
    if ($product) {
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        $cart_items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>

<div class="cart-container">
    <h2>🛒 Shopping Cart</h2>
    
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <p>Your cart is empty</p>
            <a href="index.php?action=store" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <div class="cart-items">
                <h3>Items in your cart</h3>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <?php if (!empty($item['product']['image'])): ?>
                                <img src="public/images/<?php echo htmlspecialchars($item['product']['image']); ?>" alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">📦</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['product']['name']); ?></h4>
                            <p class="price">$<?php echo number_format($item['product']['price'], 2); ?></p>
                        </div>
                        
                        <div class="item-quantity">
                            <form method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <input type="number" name="quantity" min="1" value="<?php echo $item['quantity']; ?>">
                                <button type="submit" name="update_quantity" class="btn btn-small">Update</button>
                            </form>
                        </div>
                        
                        <div class="item-subtotal">
                            <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                        </div>
                        
                        <a href="index.php?action=cart&remove=<?php echo $item['product']['id']; ?>" class="btn btn-danger btn-small">Remove</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>$<?php echo number_format($total, 2); ?></span>
                </div>
                
                <form method="POST" action="index.php?action=checkout" style="margin-top: 20px;">
                    <button type="submit" name="checkout" class="btn btn-primary btn-full">Proceed to Checkout</button>
                </form>
                
                <a href="index.php?action=store" class="btn btn-secondary btn-full" style="margin-top: 10px;">Continue Shopping</a>
            </div>
        </div>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
            header('Location: index.php?action=checkout');
            exit;
        }
        ?>
    <?php endif; ?>
</div>

<style>
.cart-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart p {
    font-size: 18px;
    color: #7f8c8d;
    margin-bottom: 20px;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 30px;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr 150px 100px 80px;
    gap: 20px;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 6px;
    overflow: hidden;
    background: white;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.item-details h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.item-details .price {
    color: #27ae60;
    font-weight: 700;
}

.item-quantity input {
    width: 60px;
    padding: 6px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.item-subtotal {
    font-size: 18px;
    color: #27ae60;
}

.cart-summary {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    height: fit-content;
    position: sticky;
    top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #ecf0f1;
}

.summary-row.total {
    border-bottom: none;
    font-size: 18px;
    font-weight: 700;
    color: #2c3e50;
    padding-top: 15px;
}

.btn-full {
    width: 100%;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 60px 1fr;
    }
    
    .cart-summary {
        position: static;
    }
}
</style>
