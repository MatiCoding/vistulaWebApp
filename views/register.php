<?php
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = ?";
        $statement = $connection->prepare($query);
        $statement->bind_param("s", $email);
        $statement->execute();
        
        if ($statement->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert new user
            $query = "INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)";
            $statement = $connection->prepare($query);
            $statement->bind_param("sssss", $full_name, $email, $password_hash, $phone, $address);
            
            if ($statement->execute()) {
                $success = 'Registration successful. <a href="index.php?action=login">Sign in here</a>';
            } else {
                $error = 'Registration error';
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Create Account</h2>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="full_name" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Phone:</label>
            <input type="tel" name="phone">
        </div>
        
        <div class="form-group">
            <label>Address:</label>
            <textarea name="address" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>
    
    <p>Already have an account? <a href="index.php?action=login">Sign in here</a></p>
</div>
