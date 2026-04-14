<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

$ordersQuery = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
$ordersResult = mysqli_query($conn, $ordersQuery);

$pageTitle = "My Orders";
include 'includes/header.php';
?>

<div class="container">
    <h1>My Orders</h1>

    <?php if (mysqli_num_rows($ordersResult) > 0): ?>
        <div class="orders-list">
            <?php while ($order = mysqli_fetch_assoc($ordersResult)): ?>
                <?php

                $itemsQuery = "SELECT * FROM order_items WHERE order_id = {$order['id']}";
                $itemsResult = mysqli_query($conn, $itemsQuery);
                ?>
                <div class="order-card">
                    <div class="order-card-header">
                        <div>
                            <h3>Order #<?php echo $order['id']; ?></h3>
                            <p class="order-date"><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn btn-small btn-success" target="_blank" title="Download Invoice">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div class="order-items-mini">
                            <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>
                                <div class="order-item-mini">
                                    <span><?php echo htmlspecialchars($item['product_name']); ?> × <?php echo $item['quantity']; ?></span>
                                    <span><?php echo formatPrice($item['subtotal']); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="order-total">
                            <strong>Total: <?php echo formatPrice($order['total_amount']); ?></strong>
                        </div>

                        <div class="order-shipping">
                            <p><strong>Shipping Address:</strong></p>
                            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>No orders yet</h3>
            <p>Start shopping to place your first order!</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>