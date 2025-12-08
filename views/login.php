<?php
global $connection;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? true : false;

    // Prepared statement for security
    $query = "SELECT id, full_name, password, is_admin FROM users WHERE email = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("s", $email);
    $statement->execute();
    $result = $statement->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Handle remember me
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $update_query = "UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?";
                $update_stmt = $connection->prepare($update_query);
                $update_stmt->bind_param("ssi", $token, $expiry, $user['id']);
                $update_stmt->execute();
                
                setcookie('remember_token', $token, strtotime('+30 days'), '/');
            }
            
            if ($user['is_admin']) {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: index.php?action=store');
            }
            exit;
        } else {
            $error = 'Incorrect password';
        }
    } else {
        $error = 'Email not found';
    }
}
?>

<div class="form-container">
    <div class="form-header">
        <h2>Sign In</h2>
        <p>Welcome back to our store</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <span class="alert-icon">⚠️</span>
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required placeholder="your@email.com" autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>
        
        <div class="form-group checkbox">
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Remember me for 30 days</label>
        </div>
        
        <button type="submit" class="btn btn-primary btn-full">Sign In</button>
    </form>
    
    <div class="form-footer">
        <p>Don't have an account? <a href="index.php?action=register">Create one now</a></p>
    </div>
</div>
