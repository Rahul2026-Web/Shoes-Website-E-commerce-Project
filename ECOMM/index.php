<?php
require_once 'includes/config.php';

$featuredQuery = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.is_featured = 1 
                  ORDER BY p.created_at DESC 
                  LIMIT 6";
$featuredResult = mysqli_query($conn, $featuredQuery);

$categoriesQuery = "SELECT * FROM categories LIMIT 5";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

$pageTitle = "Home";
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Welcome to The Shoe Vault</h1>
            <p>Find the perfect shoes for every occasion</p>
            <a href="products.php" class="btn btn-primary btn-large">Shop Now</a>
        </div>
    </div>
</div>

<div class="container">
    <?php displayMessage(); ?>

    <section class="categories-section">
        <h2 class="section-title">Shop by Category</h2>
        <div class="categories-grid">
            <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <img src="<?php echo getCategoryImage($category['image'], $category['name']); ?>" 
                         alt="<?php echo htmlspecialchars($category['name']); ?>" 
                         class="category-image">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <section class="products-section">
        <h2 class="section-title">Featured Products</h2>
        <div class="products-grid">
            <?php while ($product = mysqli_fetch_assoc($featuredResult)): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo getProductImage($product['image'], $product['name']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php if ($product['is_featured']): ?>
                            <span class="featured-badge">Featured</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary btn-sm">View Details</a>
                            <?php if (isLoggedIn() && !isAdmin()): ?>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-primary btn-sm">Add to Cart</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <br>
        <div class="text-center">
            <a href="products.php" class="btn btn-secondary">View All Products</a>
        </div>
    </section>

    <section class="features-section">
        <div class="features-grid">
            <div class="feature-box">
                <div class="feature-icon">🚚</div>
                <h3>Free Shipping</h3>
                <p>On orders over ₹1000</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🔄</div>
                <h3>Easy Returns</h3>
                <p>10-day return policy</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">💳</div>
                <h3>Secure Payment</h3>
                <p>100% secure transactions</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🏆</div>
                <h3>Quality Products</h3>
                <p>Premium quality guaranteed</p>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>