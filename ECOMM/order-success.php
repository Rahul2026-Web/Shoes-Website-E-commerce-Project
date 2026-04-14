<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['order_id'])) {
    redirect('index.php');
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

$orderQuery = "SELECT * FROM orders WHERE id = $orderId AND user_id = $userId";
$orderResult = mysqli_query($conn, $orderQuery);

if (mysqli_num_rows($orderResult) == 0) {
    redirect('index.php');
}

$order = mysqli_fetch_assoc($orderResult);

$itemsQuery = "SELECT * FROM order_items WHERE order_id = $orderId";
$itemsResult = mysqli_query($conn, $itemsQuery);

$payment_details = [];
if (!empty($order['payment_details'])) {
    $payment_details = json_decode($order['payment_details'], true);
}

$pageTitle = "Order Successful";
include 'includes/header.php';
?>

<style>
.receipt {
    background: white;
    border: 2px dashed #3498db;
    border-radius: 12px;
    padding: 2rem;
    margin: 2rem 0;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}
.receipt-header {
    text-align: center;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 1.5rem;
}
.receipt-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
}
.receipt-row:last-child {
    border-bottom: none;
}
.receipt-total {
    background: #f8f9fa;
    padding: 1rem;
    margin-top: 1rem;
    border-radius: 8px;
    font-size: 1.2rem;
    font-weight: bold;
}
.payment-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: #e8f4f8;
    color: #3498db;
    border-radius: 20px;
    font-size: 0.9rem;
}
</style>

<div class="container">
    <div class="order-success-box">
        <div class="success-icon">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your purchase. Your order has been received and is being processed.</p>

        <!-- Payment Receipt -->
        <div class="receipt">
            <div class="receipt-header">
                <h3 style="margin: 0; color: #2c3e50;"><i class="fas fa-file-invoice"></i> Payment Receipt</h3>
                <small style="color: #666;">The Shoe Vault</small>
            </div>

            <div class="receipt-row">
                <span style="color: #666;">Order ID:</span>
                <strong style="color: #3498db;">#<?php echo $order['id']; ?></strong>
            </div>

            <?php if (!empty($order['transaction_id'])): ?>
            <div class="receipt-row">
                <span style="color: #666;">Transaction ID:</span>
                <strong style="color: #3498db;"><?php echo htmlspecialchars($order['transaction_id']); ?></strong>
            </div>
            <?php endif; ?>

            <div class="receipt-row">
                <span style="color: #666;">Date & Time:</span>
                <strong><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></strong>
            </div>

            <div class="receipt-row">
                <span style="color: #666;">Payment Method:</span>
                <strong>
                    <span class="payment-badge">
                        <i class="fas fa-<?php 
                            echo $order['payment_method'] == 'Card' ? 'credit-card' : 
                                ($order['payment_method'] == 'UPI' ? 'mobile-alt' : 
                                ($order['payment_method'] == 'Wallet' ? 'wallet' : 'money-bill-wave')); 
                        ?>"></i>
                        <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
                        <?php if ($order['payment_method'] == 'Card' && isset($payment_details['card_last4'])): ?>
                            (****<?php echo $payment_details['card_last4']; ?>)
                        <?php elseif ($order['payment_method'] == 'UPI' && isset($payment_details['upi_id'])): ?>
                            (<?php echo htmlspecialchars($payment_details['upi_id']); ?>)
                        <?php elseif ($order['payment_method'] == 'Wallet' && isset($payment_details['wallet_type'])): ?>
                            (<?php echo htmlspecialchars($payment_details['wallet_type']); ?>)
                        <?php endif; ?>
                    </span>
                </strong>
            </div>

            <div class="receipt-row">
                <span style="color: #666;">Status:</span>
                <strong style="color: #27ae60;">
                    <i class="fas fa-check-circle"></i> <?php echo ucfirst($order['status']); ?>
                </strong>
            </div>

            <div class="receipt-row">
                <span style="color: #666;">Phone:</span>
                <strong><?php echo htmlspecialchars($order['phone']); ?></strong>
            </div>

            <div class="receipt-row">
                <span style="color: #666;">Shipping Address:</span>
                <strong style="text-align: right; max-width: 60%;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></strong>
            </div>

            <div class="receipt-total">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Amount:</span>
                    <span style="color: #3498db;"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <div class="order-info">
            <h2>Ordered Items</h2>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><?php echo formatPrice($item['subtotal']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="success-actions">
            <a href="invoice.php?order_id=<?php echo $order['id']; ?>" class="btn btn-success" target="_blank">
                <i class="fas fa-file-invoice"></i> Download Invoice
            </a>
            <a href="orders.php" class="btn btn-primary">View My Orders</a>
            <a href="products.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>