<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$customersQuery = "SELECT u.*, 
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as total_orders,
                   (SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE user_id = u.id AND status = 'completed') as total_spent
                   FROM users u 
                   WHERE role = 'customer' 
                   ORDER BY u.created_at DESC";
$customersResult = mysqli_query($conn, $customersQuery);

$pageTitle = "Manage Customers";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="customers.php" class="active">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <h1>Manage Customers</h1>

        <?php displayMessage(); ?>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Orders</th>
                        <th>Total Spent</th>
                        <th>Registered Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($customer = mysqli_fetch_assoc($customersResult)): ?>
                        <tr>
                            <td><?php echo $customer['id']; ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo $customer['total_orders']; ?></td>
                            <td><?php echo formatPrice($customer['total_spent']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>