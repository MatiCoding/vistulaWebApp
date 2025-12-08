<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $user_email = 'admin@store.com';
    $query = "UPDATE users SET password = ? WHERE email = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("ss", $hashed_password, $user_email);
    
    if ($statement->execute()) {
        echo '<div style="background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px; text-align: center;">';
        echo '<h2>✅ Password Updated Successfully!</h2>';
        echo '<p><strong>Generated hash:</strong></p>';
        echo 'de style="background: #f5f5f5; padding: 10px; display: block; margin: 10px 0; word-break: break-all;">' . htmlspecialchars($hashed_password) . '</code>';
        echo '<p>You can now login with:<br>';
        echo '<strong>Email:</strong> admin@store.com<br>';
        echo '<strong>Password:</strong> ' . htmlspecialchars($new_password);
        echo '</p>';
        echo '<p><a href="http://localhost/ecommerce-supermarket/" style="color: #155724; text-decoration: none; font-weight: bold;">← Back to Store</a></p>';
        echo '</div>';
        echo '<div style="text-align: center; margin-top: 20px;">';
        echo '<p style="color: #666;">You can now delete the generate_hash.php file for security.</p>';
        echo '</div>';
    } else {
        echo '<div style="background: #f8d7da; padding: 20px; text-align: center;">';
        echo '<h2>❌ Error updating password</h2>';
        echo '<p>Error: ' . $connection->error . '</p>';
        echo '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Admin Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 500px;
            margin: 80px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        button {
            width: 100%;
            background: #3498db;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        button:hover {
            background: #2980b9;
        }
        .info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Generate Admin Password Hash</h1>
        
        <div class="info">
            <strong>This tool will:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Generate a secure hash for your password</li>
                <li>Update the admin user in the database</li>
                <li>Allow you to login immediately</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Enter Admin Password:</label>
                <input type="password" id="password" name="password" required placeholder="e.g., admin123" autofocus>
            </div>
            
            <button type="submit">Generate & Update Password</button>
        </form>
    </div>
</body>
</html>
