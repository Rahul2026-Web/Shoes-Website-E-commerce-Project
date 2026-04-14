<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$statsQuery = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE role = 'customer') as total_customers,
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed') as total_revenue
";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

$ordersQuery = "
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
";
$ordersResult = mysqli_query($conn, $ordersQuery);

$lowStockQuery = "SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 10";
$lowStockResult = mysqli_query($conn, $lowStockQuery);

$pageTitle = "Admin Dashboard";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <h1>Dashboard</h1>

        <?php displayMessage(); ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <p class="stat-number"><?php echo $stats['total_customers']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Products</h3>
                <p class="stat-number"><?php echo $stats['total_products']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="stat-number"><?php echo $stats['total_orders']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p class="stat-number"><?php echo formatPrice($stats['total_revenue']); ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-section">
                <h2>Recent Orders</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <h2>Low Stock Products</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = mysqli_fetch_assoc($lowStockResult)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><span class="stock-warning"><?php echo $product['stock']; ?></span></td>
                                    <td><a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm">Update</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>