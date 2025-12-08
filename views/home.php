<div class="hero-section">
    <div class="hero-content">
        <h1>🛒 Welcome to Online Supermarket</h1>
        <p>Discover our premium selection of products at unbeatable prices</p>
        <a href="index.php?action=store" class="btn btn-primary btn-large">Browse Products</a>
    </div>
</div>

<div class="home-container">
    <section class="featured-section">
        <h2>Featured Products</h2>
        <p class="section-subtitle">Check out our latest and most popular items</p>
        
        <?php
        $products = getProducts($connection);
        // Limitar a 6 productos aleatorios
        $featured = array_slice($products, 0, 6);
        ?>
        
        <?php if (!empty($featured)): ?>
            <div class="products-grid">
                <?php foreach ($featured as $product): ?>
                    <div class="product-card">
                        <div class="product-content">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                            
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
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form method="POST" action="index.php?action=store" class="add-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <div class="quantity-selector">
                                        <input type="number" name="quantity" min="1" max="<?php echo $product['stock']; ?>" value="1">
                                    </div>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-full">🛒 Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <a href="index.php?action=login" class="btn btn-primary btn-full" style="display: block; text-align: center; text-decoration: none;">🔑 Sign in to Buy</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-disabled btn-full" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="index.php?action=store" class="btn btn-secondary btn-large">View All Products →</a>
        </div>
    </section>

    <section class="info-section">
        <div class="info-grid">
            <div class="info-card">
                <div class="info-icon">🚚</div>
                <h3>Fast Delivery</h3>
                <p>Get your products delivered quickly and safely</p>
            </div>
            <div class="info-card">
                <div class="info-icon">💳</div>
                <h3>Secure Payment</h3>
                <p>Your payment information is always protected</p>
            </div>
            <div class="info-card">
                <div class="info-icon">⭐</div>
                <h3>Quality Products</h3>
                <p>We carefully select the best products for you</p>
            </div>
        </div>
    </section>
</div>

<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 20px;
    text-align: center;
    border-radius: 12px;
    margin-bottom: 60px;
}

.hero-content h1 {
    font-size: 48px;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 20px;
    margin-bottom: 30px;
    opacity: 0.95;
}

.btn-large {
    padding: 15px 40px;
    font-size: 16px;
}

.home-container {
    max-width: 1200px;
    margin: 0 auto;
}

.featured-section {
    margin-bottom: 80px;
}

.featured-section h2 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.section-subtitle {
    color: #7f8c8d;
    font-size: 16px;
    margin-bottom: 40px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #f39c12;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.stock-badge.out {
    background: #e74c3c;
}

.product-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-content h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #2c3e50;
}

.product-description {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 15px;
    flex: 1;
}

.product-meta {
    margin-bottom: 15px;
}

.category-tag {
    display: inline-block;
    background: #ecf0f1;
    color: #2c3e50;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
    margin-bottom: 15px;
}

.price {
    font-size: 22px;
    font-weight: 700;
    color: #27ae60;
}

.stock-info {
    color: #7f8c8d;
    font-size: 12px;
}

.add-cart-form {
    padding: 0 20px 20px 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quantity-selector input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-align: center;
}

.btn-full {
    width: 100%;
}

.btn-disabled {
    background: #95a5a6;
    cursor: not-allowed;
}

.info-section {
    background: #f8f9fa;
    padding: 60px 20px;
    border-radius: 12px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.info-card {
    background: white;
    padding: 40px 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.info-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.info-card h3 {
    font-size: 20px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.info-card p {
    color: #7f8c8d;
    font-size: 14px;
}

@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 32px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
}
</style>
