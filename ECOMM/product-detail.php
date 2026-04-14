<?php
require_once 'includes/config.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('products.php');
}

$productId = (int)$_GET['id'];

$productQuery = "SELECT p.*, c.name as category_name FROM products p 
                 JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = $productId";
$productResult = mysqli_query($conn, $productQuery);

if (mysqli_num_rows($productResult) == 0) {
    redirect('products.php');
}

$product = mysqli_fetch_assoc($productResult);

$relatedQuery = "SELECT * FROM products 
                 WHERE category_id = {$product['category_id']} 
                 AND id != $productId 
                 LIMIT 4";
$relatedResult = mysqli_query($conn, $relatedQuery);

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<div class="container">
    <div class="breadcrumb">
        <a href="index.php">Home</a> /
        <a href="products.php">Products</a> /
        <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a> /
        <span><?php echo htmlspecialchars($product['name']); ?></span>
    </div>

    <div class="product-detail-layout">
        <div class="product-detail-image">
            <img src="<?php echo getProductImage($product['image'], $product['name']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 style="width: 100%; border-radius: 10px;">
        </div>

        <div class="product-detail-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-category-detail">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>

            <div class="product-price-detail">
                <span class="price"><?php echo formatPrice($product['price']); ?></span>
            </div>

            <div class="product-stock-detail">
                <?php if ($product['stock'] > 0): ?>
                    <span class="in-stock">✓ In Stock (<?php echo $product['stock']; ?> available)</span>
                <?php else: ?>
                    <span class="out-of-stock">✗ Out of Stock</span>
                <?php endif; ?>
            </div>

            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <?php if (isLoggedIn() && !isAdmin() && $product['stock'] > 0): ?>
                <div class="product-actions-detail">
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                    <button onclick="addToCartWithQuantity(<?php echo $product['id']; ?>)" class="btn btn-primary btn-large">
                        Add to Cart
                    </button>
                </div>
            <?php elseif (!isLoggedIn()): ?>
                <div class="alert alert-info">
                    <a href="login.php">Login</a> to add products to cart
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (mysqli_num_rows($relatedResult) > 0): ?>
        <section class="related-products">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid">
                <?php while ($related = mysqli_fetch_assoc($relatedResult)): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo getProductImage($related['image'], $related['name']); ?>"
                                alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <p class="product-price"><?php echo formatPrice($related['price']); ?></p>
                            <div class="product-actions">
                                <a href="product-detail.php?id=<?php echo $related['id']; ?>"
                                    class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>