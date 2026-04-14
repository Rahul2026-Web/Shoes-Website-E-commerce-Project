<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitize($_POST['status']);

    $updateQuery = "UPDATE orders SET status = '$status' WHERE id = $orderId";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['message'] = "Order #$orderId status updated to " . ucfirst($status) . " successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    header("Location: " . BASE_URL . "admin/orders.php?view=" . $orderId);
    exit();
}

$ordersQuery = "SELECT o.*, u.name as customer_name, u.email as customer_email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC";
$ordersResult = mysqli_query($conn, $ordersQuery);

$viewOrder = null;
$orderItems = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];

    mysqli_query($conn, "SET SESSION query_cache_type = OFF");

    $viewQuery = "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone 
                  FROM orders o 
                  JOIN users u ON o.user_id = u.id 
                  WHERE o.id = $viewId";
    $viewResult = mysqli_query($conn, $viewQuery);
    $viewOrder = mysqli_fetch_assoc($viewResult);

    if ($viewOrder) {
        $itemsQuery = "SELECT * FROM order_items WHERE order_id = $viewId";
        $orderItems = mysqli_query($conn, $itemsQuery);
    }
}

$pageTitle = "Manage Orders";
include '../includes/header.php';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <ul class="admin-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php" class="active">Orders</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li><a href="reports.php">Revenue Reports</a></li>
            <li><a href="contact-messages.php">Contact Messages</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <h1>Manage Orders</h1>

        <?php displayMessage(); ?>

        <?php if ($viewOrder): ?>
            <div class="order-detail-box">
                <div class="order-header">
                    <h2>Order #<?php echo $viewOrder['id']; ?></h2>
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                </div>

                <div class="order-info-grid">
                    <div class="info-section">
                        <h3>Customer Information</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($viewOrder['customer_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($viewOrder['customer_email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($viewOrder['phone']); ?></p>
                        <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($viewOrder['shipping_address'])); ?></p>
                    </div>

                    <div class="info-section">
                        <h3>Order Information</h3>
                        <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($viewOrder['created_at'])); ?></p>
                        <p><strong>Status:</strong> <span class="status-badge status-<?php echo $viewOrder['status']; ?>"><?php echo ucfirst($viewOrder['status']); ?></span></p>
                        <p><strong>Total Amount:</strong> <?php echo formatPrice($viewOrder['total_amount']); ?></p>
                    </div>
                </div>

                <h3>Order Items</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = mysqli_fetch_assoc($orderItems)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo formatPrice($item['subtotal']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong><?php echo formatPrice($viewOrder['total_amount']); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="order-actions">
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?php echo $viewOrder['id']; ?>">
                        <label for="status">Update Status:</label>
                        <select name="status" id="status">
                            <option value="pending" <?php echo $viewOrder['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $viewOrder['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="completed" <?php echo $viewOrder['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $viewOrder['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td class="table-actions">
                                <a href="orders.php?view=<?php echo $order['id']; ?>" class="btn btn-sm">View Details</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
