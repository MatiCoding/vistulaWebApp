<?php
session_start();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Clear session data from database
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "UPDATE users SET remember_token = NULL, token_expiry = NULL WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("i", $user_id);
    $statement->execute();
}

// Redirect to home
header('Location: ../index.php');
exit;
?>
