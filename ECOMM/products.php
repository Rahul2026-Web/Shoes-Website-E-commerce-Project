<?php
require_once 'includes/config.php';

$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$whereClause = "WHERE 1=1";
if ($category_filter > 0) {
    $whereClause .= " AND p.category_id = $category_filter";
}
if (!empty($search)) {
    $whereClause .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

$productsQuery = "SELECT p.*, c.name as category_name FROM products p 
                  JOIN categories c ON p.category_id = c.id 
                  $whereClause
                  ORDER BY p.created_at DESC";
$productsResult = mysqli_query($conn, $productsQuery);

$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesResult = mysqli_query($conn, $categoriesQuery);

$pageTitle = "Products";
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Our Products</h1>
    </div>

    <div class="products-layout">
        <aside class="products-sidebar">
            <div class="filter-box">
                <h3>Categories</h3>
                <ul class="category-filter">
                    <li>
                        <a href="products.php" class="<?php echo $category_filter == 0 ? 'active' : ''; ?>">
                            All Categories
                        </a>
                    </li>
                    <?php while ($category = mysqli_fetch_assoc($categoriesResult)): ?>
                        <li>
                            <a href="products.php?category=<?php echo $category['id']; ?>"
                                class="<?php echo $category_filter == $category['id'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="filter-box">
                <h3>Search Products</h3>
                <form method="GET" action="products.php">
                    <?php if ($category_filter > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="Search..."
                        value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                </form>
            </div>
        </aside>

        <div class="products-main">
            <?php if (mysqli_num_rows($productsResult) > 0): ?>
                <div class="products-grid">
                    <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo getProductImage($product['image'], $product['name']); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php if ($product['is_featured']): ?>
                                    <span class="featured-badge">Featured</span>
                                <?php endif; ?>
                                <?php if ($product['stock'] < 1): ?>
                                    <span class="out-of-stock-badge">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-price"><?php echo formatPrice($product['price']); ?></p>
                                <p class="product-stock">Stock: <?php echo $product['stock']; ?></p>
                                <div class="product-actions">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>"
                                        class="btn btn-secondary btn-sm btn-block">View Details</a>
                                    <?php if (isLoggedIn() && !isAdmin() && $product['stock'] > 0): ?>
                                        <button onclick="addToCart(<?php echo $product['id']; ?>)"
                                            class="btn btn-primary btn-sm btn-block">Add to Cart</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filter criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>