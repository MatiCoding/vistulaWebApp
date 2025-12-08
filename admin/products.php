<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkAdmin();

$message = '';

// Add product
if (isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    
    $query = "INSERT INTO products (name, description, price, stock, category_id) VALUES (?, ?, ?, ?, ?)";
    $statement = $connection->prepare($query);
    $statement->bind_param("ssdii", $name, $description, $price, $stock, $category_id);
    
    if ($statement->execute()) {
        if (empty($message)) {
            $message = '<div class="alert alert-success">✅ Product added successfully</div>';
        }
    } else {
        $message = '<div class="alert alert-error">❌ Error adding product</div>';
    }
}

// Update product
if (isset($_POST['update_product'])) {
    $product_id = (int) $_POST['product_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = (float) $_POST['price'];
    $stock = (int) $_POST['stock'];
    $category_id = (int) $_POST['category_id'];
    
    $query = "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ? WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("ssdiii", $name, $description, $price, $stock, $category_id, $product_id);
    
    if ($statement->execute()) {
        if (empty($message)) {
            $message = '<div class="alert alert-success">✅ Product updated successfully</div>';
        }
    } else {
        $message = '<div class="alert alert-error">❌ Error updating product</div>';
    }
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    
    $query = "DELETE FROM products WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("i", $id);
    
    if ($statement->execute()) {
        $message = '<div class="alert alert-success">✅ Product deleted successfully</div>';
    }
}

$products = getProducts($connection);
$categories_result = $connection->query("SELECT * FROM categories");
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Get product to edit
$edit_product = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $query = "SELECT * FROM products WHERE id = ?";
    $statement = $connection->prepare($query);
    $statement->bind_param("i", $edit_id);
    $statement->execute();
    $edit_product = $statement->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../public/css/styles.css">
    <style>
        .edit-view { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
        .form-section { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-section h3 { margin-top: 0; color: #2c3e50; }
        .cancel-edit { margin-top: 10px; }
        @media (max-width: 768px) {
            .edit-view { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-nav">
            <a href="dashboard.php" class="logo">🛒 Online Supermarket - Admin</a>
            <ul class="menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="orders.php">Manage Orders</a></li>
                <li><a href="products.php">Manage Products</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Product Management</h2>
        
        <?php echo $message; ?>
        
        <?php if ($edit_product): ?>
            <!-- EDIT MODE -->
            <div class="edit-view">
                <div class="form-section">
                    <h3>✏️ Edit Product</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                        
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Description:</label>
                            <textarea name="description" rows="4" required><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" name="price" step="0.01" value="<?php echo $edit_product['price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Stock:</label>
                            <input type="number" name="stock" value="<?php echo $edit_product['stock']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Category:</label>
                            <select name="category_id" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $edit_product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_product" class="btn btn-primary">Save Changes</button>
                        <a href="products.php" class="btn btn-secondary cancel-edit">Cancel Edit</a>
                    </form>
                </div>
                
                <div class="form-section">
                    <h3>📋 Product Details</h3>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px;">
                        <p><strong>ID:</strong> <?php echo $edit_product['id']; ?></p>
                        <p><strong>Current Stock:</strong> <?php echo $edit_product['stock']; ?> units</p>
                        <p><strong>Current Price:</strong> $<?php echo number_format($edit_product['price'], 2); ?></p>
                        <p><strong>Category:</strong> <?php 
                            $cat = $connection->query("SELECT name FROM categories WHERE id = " . $edit_product['category_id']);
                            $c = $cat->fetch_assoc();
                            echo htmlspecialchars($c['name']);
                        ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- ADD MODE -->
            <div class="form-container" style="margin-bottom: 30px;">
                <h3>Add New Product</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" required placeholder="Product name">
                    </div>
                    
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" rows="3" placeholder="Product description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Price:</label>
                        <input type="number" name="price" step="0.01" required placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label>Stock:</label>
                        <input type="number" name="stock" required placeholder="0">
                    </div>
                    
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        <?php endif; ?>

        <h3>Existing Products</h3>
        <div style="overflow-x: auto;">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php 
                            $cat = $connection->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
                            $c = $cat->fetch_assoc();
                            echo htmlspecialchars($c['name']);
                        ?></td>
                        <td>
                            <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-info btn-small">✏️ Edit</a>
                            <a href="products.php?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-small" onclick="return confirm('Delete this product?')">🗑️ Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Online Supermarket. Admin Panel.</p>
    </footer>
</body>
</html>
