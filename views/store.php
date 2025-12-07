<?php if (isset($_SESSION['cart_success'])): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        ✅ Product added to cart successfully! <a href="index.php?action=cart" style="color: #155724; font-weight: bold;">View Cart</a>
    </div>
    <?php unset($_SESSION['cart_success']); ?>
<?php endif; ?>

<?php 
$products = getProducts($connection);
$category = isset($_GET['category']) ? (int)$_GET['category'] : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';

// Advanced filtering - SIN PRECIO
$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

if ($category) {
    $query .= " AND category_id = ?";
    $params[] = $category;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (name LIKE ? OR description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

// SIEMPRE aplicar sorting
switch($sort) {
    case 'price_asc':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY price DESC";
        break;
    case 'name_desc':
        $query .= " ORDER BY name DESC";
        break;
    default:
        $query .= " ORDER BY name ASC";
}

$statement = $connection->prepare($query);
if (!empty($params)) {
    $statement->bind_param($types, ...$params);
}
$statement->execute();
$products = $statement->get_result()->fetch_all(MYSQLI_ASSOC);

// Get categories
$categories = getCategories($connection);
?>

<div class="store-container">
    <div class="store-header">
        <h2>🛒 Store</h2>
        <p class="store-subtitle">Discover our premium selection of products</p>
    </div>
    
    <!-- Search Bar -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <input type="hidden" name="action" value="store">
            <div class="search-input-wrapper">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="search-btn">🔍</button>
            </div>
        </form>
    </div>
    
    <!-- Filters & Categories -->
    <div class="filters-section">
        <div class="filter-column categories-filter">
            <h3>📦 Categories</h3>
            <div class="categories-list">
                <a href="index.php?action=store" class="category-item <?php echo !$category ? 'active' : ''; ?>">
                    All Products
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="index.php?action=store&category=<?php echo $cat['id']; ?>" 
                       class="category-item <?php echo $category == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
                
        <!-- Sort -->
        <div class="filter-column sort-filter">
            <h3>⬇️ Sort By</h3>
            <form method="GET" class="sort-form">
                <input type="hidden" name="action" value="store">
                <?php if ($category): ?>
                    <input type="hidden" name="category" value="<?php echo $category; ?>">
                <?php endif; ?>
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                
                <select name="sort" onchange="this.form.submit()" class="sort-select">
                    <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                    <option value="price_asc" <?php echo $sort == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_desc" <?php echo $sort == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                </select>
            </form>
        </div>
    </div>
    
    <!-- Products Grid -->
    <div class="products-section">
        <h3 class="results-count"><?php echo count($products); ?> Product<?php echo count($products) != 1 ? 's' : ''; ?> Found</h3>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>😞 No products found matching your criteria</p>
                <a href="index.php?action=store" class="btn btn-primary">View All Products</a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-content">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                            
                            <div class="product-meta">
                                <span class="category-tag"><?php 
                                    $cat = $connection->query("SELECT name FROM categories WHERE id = " . $product['category_id']);
                                    $c = $cat->fetch_assoc();
                                    echo htmlspecialchars($c['name']);
                                ?></span>
                            </div>
                            
                            <div class="product-footer">
                                <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="stock-info"><?php echo $product['stock']; ?> in stock</span>
                            </div>
                        </div>
                        
                        <?php if ($product['stock'] > 0): ?>
                            <form method="POST" action="index.php?action=store" class="add-cart-form">
                                <input type="hidden" name="add_to_cart" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <div class="quantity-selector">
                                    <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                                </div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button type="submit" name="add_to_cart" value="1" class="btn btn-primary btn-full">🛒 Add to Cart</button>
                                <?php else: ?>
                                    <a href="index.php?action=login" class="btn btn-primary btn-full" style="display: block; text-align: center; text-decoration: none;">🔑 Sign in to Buy</a>
                                <?php endif; ?>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-disabled btn-full" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.store-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.store-header {
    text-align: center;
    margin-bottom: 40px;
}

.store-header h2 {
    font-size: 2.5em;
    margin: 0 0 10px 0;
    color: #2c3e50;
}

.store-subtitle {
    color: #7f8c8d;
    font-size: 1.1em;
    margin: 0;
}

.search-section {
    margin-bottom: 30px;
}

.search-form {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.search-input-wrapper {
    display: flex;
    gap: 8px;
    width: 100%;
    max-width: 500px;
}

.search-input {
    flex: 1;
    padding: 12px;
    border: 2px solid #ecf0f1;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #3498db;
}

.search-btn {
    padding: 12px 20px;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}

.search-btn:hover {
    background: #2980b9;
}

.filters-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.filter-column {
    display: flex;
    flex-direction: column;
}

.filter-column h3 {
    margin: 0 0 15px 0;
    font-size: 1.2em;
    color: #2c3e50;
}

.categories-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.category-item {
    padding: 10px 12px;
    background: white;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
    color: #2c3e50;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
}

.category-item:hover {
    background: #ecf0f1;
    border-color: #3498db;
}

.category-item.active {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

.sort-select {
    padding: 10px;
    border: 1px solid #ecf0f1;
    border-radius: 6px;
    background: white;
    color: #2c3e50;
    font-size: 14px;
    cursor: pointer;
}

.products-section {
    margin-bottom: 40px;
}

.results-count {
    font-size: 1.1em;
    color: #2c3e50;
    margin-bottom: 20px;
}

.no-products {
    text-align: center;
    padding: 60px 20px;
}

.no-products p {
    font-size: 1.2em;
    color: #7f8c8d;
    margin-bottom: 20px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.product-card {
    background: white;
    border: 1px solid #ecf0f1;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s;
    display: flex;
    flex-direction: column;
    padding: 15px;
}

.product-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.product-content h3 {
    margin: 0 0 10px 0;
    font-size: 1.1em;
    color: #2c3e50;
}

.product-description {
    color: #7f8c8d;
    font-size: 0.9em;
    margin: 0 0 10px 0;
    flex: 1;
}

.product-meta {
    margin-bottom: 10px;
}

.category-tag {
    display: inline-block;
    background: #ecf0f1;
    color: #2c3e50;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85em;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 10px;
    border-top: 1px solid #ecf0f1;
    margin-bottom: 10px;
}

.price {
    font-size: 1.3em;
    font-weight: bold;
    color: #27ae60;
}

.stock-info {
    font-size: 0.85em;
    color: #7f8c8d;
}

.add-cart-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quantity-selector {
    display: flex;
    align-items: center;
}

.quantity-selector input {
    width: 60px;
    padding: 8px;
    border: 1px solid #ecf0f1;
    border-radius: 4px;
    text-align: center;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-primary:hover {
    background: #2980b9;
}

.btn-disabled {
    background: #bdc3c7;
    color: white;
    cursor: not-allowed;
}

.btn-full {
    width: 100%;
}

@media (max-width: 768px) {
    .filters-section {
        grid-template-columns: 1fr;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}
</style>
