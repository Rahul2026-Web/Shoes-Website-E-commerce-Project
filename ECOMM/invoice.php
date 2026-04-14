<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['order_id'])) {
    redirect('orders.php');
}

$orderId = (int)$_GET['order_id'];
$userId = $_SESSION['user_id'];

$orderQuery = "SELECT o.*, u.name, u.email FROM orders o 
               JOIN users u ON o.user_id = u.id 
               WHERE o.id = $orderId AND o.user_id = $userId";
$orderResult = mysqli_query($conn, $orderQuery);

if (mysqli_num_rows($orderResult) == 0) {
    redirect('orders.php');
}

$order = mysqli_fetch_assoc($orderResult);

$itemsQuery = "SELECT * FROM order_items WHERE order_id = $orderId";
$itemsResult = mysqli_query($conn, $itemsQuery);

$payment_details = [];
if (!empty($order['payment_details'])) {
    $payment_details = json_decode($order['payment_details'], true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Order #<?php echo $order['id']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3498db;
        }
        .company-info h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 14px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            color: #3498db;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .invoice-details p {
            color: #666;
            margin: 5px 0;
        }
        .invoice-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .info-box h3 {
            color: #2c3e50;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .info-box p {
            color: #555;
            line-height: 1.6;
            margin: 5px 0;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .invoice-table th {
            background: #3498db;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        .invoice-table tbody tr:hover {
            background: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .invoice-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .summary-box {
            min-width: 300px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .summary-total {
            background: #3498db;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
        }
        .payment-info {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .payment-info strong {
            color: #2c3e50;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .print-button:hover {
            background: #2980b9;
        }
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #95a5a6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .back-button:hover {
            background: #7f8c8d;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                padding: 20px;
            }
            .print-button, .back-button {
                display: none;
            }
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-processing {
            background: #fff3cd;
            color: #856404;
        }
        .status-pending {
            background: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <a href="<?php echo BASE_URL; ?>orders.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
    <button onclick="window.print()" class="print-button">
        <i class="fas fa-print"></i> Print Invoice
    </button>

    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-info">
                <h1><i class="fas fa-shoe-prints"></i> The Shoe Vault</h1>
                <p>Your trusted online destination for quality footwear</p>
                <p>Email: info@theshoevault.com</p>
                <p>Phone: +91 1234567890</p>
                <p>Address: City Center, Patna</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Order #<?php echo $order['id']; ?></strong></p>
                <p>Date: <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
                <p>Status: <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
            </div>
        </div>

        <div class="invoice-info">
            <div class="info-box">
                <h3><i class="fas fa-user"></i> Bill To</h3>
                <p><strong><?php echo htmlspecialchars($order['name']); ?></strong></p>
                <p><?php echo htmlspecialchars($order['email']); ?></p>
                <p><?php echo htmlspecialchars($order['phone']); ?></p>
            </div>
            <div class="info-box">
                <h3><i class="fas fa-shipping-fast"></i> Ship To</h3>
                <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            </div>
        </div>

        <?php if (!empty($order['transaction_id'])): ?>
        <div class="payment-info">
            <strong><i class="fas fa-credit-card"></i> Payment Information:</strong><br>
            Transaction ID: <?php echo htmlspecialchars($order['transaction_id']); ?><br>
            Payment Method: <?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?>
            <?php if ($order['payment_method'] == 'Card' && isset($payment_details['card_last4'])): ?>
                (Card ending in <?php echo $payment_details['card_last4']; ?>)
            <?php elseif ($order['payment_method'] == 'UPI' && isset($payment_details['upi_id'])): ?>
                (<?php echo htmlspecialchars($payment_details['upi_id']); ?>)
            <?php elseif ($order['payment_method'] == 'Wallet' && isset($payment_details['wallet_type'])): ?>
                (<?php echo htmlspecialchars($payment_details['wallet_type']); ?>)
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $itemNumber = 1;
                $subtotal = 0;
                while ($item = mysqli_fetch_assoc($itemsResult)): 
                    $subtotal += $item['subtotal'];
                ?>
                    <tr>
                        <td><?php echo $itemNumber++; ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-right"><?php echo formatPrice($item['price']); ?></td>
                        <td class="text-right"><?php echo formatPrice($item['subtotal']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="invoice-summary">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong><?php echo formatPrice($subtotal); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <strong>Free</strong>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <strong>Included</strong>
                </div>
                <div class="summary-total">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Amount:</span>
                        <span><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>For any queries, please contact us at info@theshoevault.com or call +91 1234567890</p>
        </div>
    </div>

    <script>

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
