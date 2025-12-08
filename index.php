<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// PROCESAR CARRITO PRIMERO (sin importar el action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    error_log("DEBUG: POST recibido, add_to_cart = " . $_POST['add_to_cart']);
    error_log("DEBUG: user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NO EXISTE'));
    
    if (isset($_SESSION['user_id'])) {
        $product_id = (int) $_POST['product_id'];
        $quantity = (int) $_POST['quantity'];
        
        error_log("DEBUG: Agregando producto $product_id cantidad $quantity");
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        $_SESSION['cart_success'] = true;
        error_log("DEBUG: cart_success establecido, carrito = " . print_r($_SESSION['cart'], true));
        
        // Redirige de vuelta a store
        $redirect = 'index.php?action=store';
        if (isset($_GET['category'])) {
            $redirect .= '&category=' . $_GET['category'];
        }
        if (isset($_GET['search'])) {
            $redirect .= '&search=' . $_GET['search'];
        }
        error_log("DEBUG: Redirigiendo a: " . $redirect);
        header('Location: ' . $redirect);
        exit;
    } else {
        $_SESSION['login_required'] = true;
        header('Location: index.php?action=login');
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : 'home';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Supermarket</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container-nav">
            <a href="index.php" class="logo">🛒 Online Supermarket</a>
            <ul class="menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?action=store">Store</a></li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php?action=cart">Cart</a></li>
                    <li><a href="index.php?action=orders">My Orders</a></li>
                    <?php if ($_SESSION['is_admin']): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Panel</a></li>
                    <?php endif; ?>
                    <li class="user-menu">
                        <span class="user-greeting">👤 <?php echo substr($_SESSION['full_name'], 0, 20); ?></span>
                        <div class="user-dropdown">
                            <a href="index.php?action=profile">👤 My Profile</a>
                            <a href="index.php?action=orders">📦 My Orders</a>
                            <a href="logout.php">🚪 Logout</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="index.php?action=login">Login</a></li>
                    <li><a href="index.php?action=register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container">
        <?php
        switch($action) {
            case 'login':
                include 'views/login.php';
                break;
            case 'register':
                include 'views/register.php';
                break;
            case 'store':
                include 'views/store.php';
                break;
            case 'cart':
                checkLogin();
                include 'views/cart.php';
                break;
            case 'orders':
                checkLogin();
                include 'views/orders.php';
                break;
            case 'profile':
                checkLogin();
                include 'views/profile.php';
                break;
            case 'checkout':
                checkLogin();
                include 'views/checkout.php';
                break;
            case 'order_success':
                checkLogin();
                include 'views/order_success.php';
                break;
            default:
                include 'views/home.php';
        }
        ?>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Online Supermarket. All rights reserved.</p>
    </footer>

    <script src="public/js/script.js"></script>
</body>
</html>
