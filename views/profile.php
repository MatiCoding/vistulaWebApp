<?php
checkLogin();

$user_id = $_SESSION['user_id'];
$message = '';
$success = '';

// Get current user data
$query = "SELECT * FROM users WHERE id = ?";
$statement = $connection->prepare($query);
$statement->bind_param("i", $user_id);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $email = sanitize($_POST['email']);
    
    // Check if email is unique (if changed)
    if ($email !== $user['email']) {
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = '<div class="alert alert-error">❌ This email is already in use</div>';
        }
    }
    
    if (empty($message)) {
        $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $success = '<div class="alert alert-success">✅ Profile updated successfully</div>';
            // Refresh user data
            $user['full_name'] = $full_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['address'] = $address;
        } else {
            $message = '<div class="alert alert-error">❌ Error updating profile</div>';
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $message = '<div class="alert alert-error">❌ Current password is incorrect</div>';
    } else if ($new_password !== $confirm_password) {
        $message = '<div class="alert alert-error">❌ New passwords do not match</div>';
    } else if (strlen($new_password) < 6) {
        $message = '<div class="alert alert-error">❌ Password must be at least 6 characters</div>';
    } else {
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $pwd_query = "UPDATE users SET password = ? WHERE id = ?";
        $pwd_stmt = $connection->prepare($pwd_query);
        $pwd_stmt->bind_param("si", $password_hash, $user_id);
        
        if ($pwd_stmt->execute()) {
            $success = '<div class="alert alert-success">✅ Password changed successfully</div>';
        } else {
            $message = '<div class="alert alert-error">❌ Error changing password</div>';
        }
    }
}
?>

<div class="profile-container">
    <div class="profile-header">
        <h2>👤 My Account</h2>
        <p>Manage your profile and account settings</p>
    </div>
    
    <?php echo $message . $success; ?>
    
    <div class="profile-grid">
        <!-- Update Profile -->
        <div class="profile-card">
            <h3>📝 Personal Information</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" rows="4"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="profile-card">
            <h3>🔐 Security</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password:</label>
                    <input type="password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" required placeholder="Min. 6 characters">
                </div>
                
                <div class="form-group">
                    <label>Confirm New Password:</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>
    
    <!-- Account Info -->
    <div class="account-info">
        <h3>📊 Account Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">Member Since:</span>
                <span class="value"><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></span>
            </div>
            <div class="info-item">
                <span class="label">Account Status:</span>
                <span class="value"><?php echo $user['is_admin'] ? '👨‍💼 Administrator' : '👤 Regular User'; ?></span>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-header {
    text-align: center;
    margin-bottom: 40px;
}

.profile-header h2 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.profile-header p {
    color: #7f8c8d;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.profile-card {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #ecf0f1;
}

.profile-card h3 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 18px;
}

.account-info {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    border: 1px solid #ecf0f1;
}

.account-info h3 {
    color: #2c3e50;
    margin-bottom: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item .label {
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.info-item .value {
    font-size: 16px;
    color: #2c3e50;
    font-weight: 600;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 20px;
    }
    
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
</style>
