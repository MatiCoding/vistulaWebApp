<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce_supermarket');

// Create connection
$connection = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($connection->connect_error) {
    die('Connection Error: ' . $connection->connect_error);
}

// Set charset to UTF-8
$connection->set_charset('utf8mb4');

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && !isset($_SESSION['user_id'])) {
    $token = $_COOKIE['remember_token'];
    $query = "SELECT id, full_name, is_admin FROM users WHERE remember_token = ? AND token_expiry > NOW()";
    $statement = $connection->prepare($query);
    $statement->bind_param("s", $token);
    $statement->execute();
    $result = $statement->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['is_admin'] = $user['is_admin'];
    }
}
?>
