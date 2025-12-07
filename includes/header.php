<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
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
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="index.php?action=store">Store</a></li>
                    <li><a href="index.php?action=cart">Cart</a></li>
                    <li><a href="index.php?action=orders">My Orders</a></li>
                    <?php if ($_SESSION['is_admin']): ?>
                        <li><a href="admin/dashboard.php" class="admin-link">Admin Panel</a></li>
                    <?php endif; ?>
                    <li class="user-menu">
                        <span class="user-greeting">👤 <?php echo substr($_SESSION['full_name'], 0, 20); ?></span>
                        <div class="user-dropdown">
                            <a href="index.php?action=profile">My Profile</a>
                            <a href="index.php?action=orders">My Orders</a>
                            <a href="logout.php">Logout</a>
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
