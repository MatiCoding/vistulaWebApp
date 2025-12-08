<?php

/**
 * Check if user is logged in
 */
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?action=login');
        exit;
    }
}

/**
 * Check if user is admin
 */
function checkAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Sanitize user input
 */
function sanitize($input) {
    global $connection;
    return $connection->real_escape_string(htmlspecialchars(trim($input)));
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Get all products from database
 */
function getProducts($connection) {
    $query = "SELECT * FROM products";
    $result = $connection->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get product by ID
 */
function getProductById($connection, $id) {
    $query = "SELECT * FROM products WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("i", $id);
    $statement->execute();
    return $statement->get_result()->fetch_assoc();
}

/**
 * Update product stock
 */
function updateStock($connection, $product_id, $quantity) {
    $query = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("ii", $quantity, $product_id);
    return $statement->execute();
}

/**
 * Get all categories
 */
function getCategories($connection) {
    $query = "SELECT * FROM categories";
    $result = $connection->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

?>
