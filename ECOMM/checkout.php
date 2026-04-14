<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

$cartQuery = "SELECT c.*, p.name, p.price, p.stock 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $userId";
$cartResult = mysqli_query($conn, $cartQuery);

if (mysqli_num_rows($cartResult) == 0) {
    redirect('cart.php');
}

$total = 0;
$cartItems = [];
while ($item = mysqli_fetch_assoc($cartResult)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cartItems[] = $item;
}

$userQuery = "SELECT * FROM users WHERE id = $userId";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $payment_method = sanitize($_POST['payment_method']);

    $errors = [];
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    if (empty($address)) {
        $errors[] = "Shipping address is required";
    }
    if (empty($payment_method)) {
        $errors[] = "Please select a payment method";
    }

    $payment_details = [];
    $transaction_id = 'TXN' . strtoupper(uniqid()) . time();

    if ($payment_method == 'Card') {
        $card_number = preg_replace('/\s+/', '', sanitize($_POST['card_number']));
        $card_holder = sanitize($_POST['card_holder']);
        $expiry_month = sanitize($_POST['expiry_month']);
        $expiry_year = sanitize($_POST['expiry_year']);
        $cvv = sanitize($_POST['cvv']);

        if (!empty($card_number) && is_numeric($card_number) && strlen($card_number) >= 13 && strlen($card_number) <= 19 && !empty($card_holder) && !empty($expiry_month) && !empty($expiry_year) && !empty($cvv)) {

            $card_type = 'Credit Card';
            if (preg_match('/^4/', $card_number)) {
                $card_type = 'Visa';
            } elseif (preg_match('/^5[1-5]/', $card_number)) {
                $card_type = 'Mastercard';
            } elseif (preg_match('/^3[47]/', $card_number)) {
                $card_type = 'Amex';
            } elseif (preg_match('/^6(?:011|5)/', $card_number)) {
                $card_type = 'Discover';
            }

            $payment_details = [
                'card_last4' => substr($card_number, -4),
                'card_holder' => $card_holder,
                'card_type' => $card_type,
                'expiry' => "$expiry_month/$expiry_year"
            ];
        } else {
            $errors[] = 'Invalid card details. Please enter valid card information.';
        }
    } elseif ($payment_method == 'UPI') {
        $upi_id = sanitize($_POST['upi_id']);
        if (filter_var($upi_id, FILTER_VALIDATE_EMAIL) || strpos($upi_id, '@') !== false) {
            $payment_details = ['upi_id' => $upi_id];
        } else {
            $errors[] = 'Invalid UPI ID format';
        }
    } elseif ($payment_method == 'Wallet') {
        $wallet_type = sanitize($_POST['wallet_type']);
        $wallet_mobile = sanitize($_POST['wallet_mobile']);
        if (!empty($wallet_mobile) && preg_match('/^[0-9]{10}$/', $wallet_mobile)) {
            $payment_details = [
                'wallet_type' => $wallet_type,
                'wallet_mobile' => $wallet_mobile
            ];
        } else {
            $errors[] = 'Invalid wallet details';
        }
    } elseif ($payment_method == 'COD') {
        $payment_details = ['method' => 'Cash on Delivery'];
        $transaction_id = 'COD' . strtoupper(uniqid()) . time();
    }

    if (empty($errors)) {

        mysqli_begin_transaction($conn);

        try {
            $payment_details_json = mysqli_real_escape_string($conn, json_encode($payment_details));

            $insertOrderQuery = "INSERT INTO orders (user_id, total_amount, shipping_address, phone, status, payment_method, payment_details, transaction_id) 
                                VALUES ($userId, $total, '$address', '$phone', 'processing', '$payment_method', '$payment_details_json', '$transaction_id')";

            if (!mysqli_query($conn, $insertOrderQuery)) {
                throw new Exception("Order creation failed: " . mysqli_error($conn));
            }

            $orderId = mysqli_insert_id($conn);

            foreach ($cartItems as $item) {
                $insertItemQuery = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                                   VALUES ($orderId, {$item['product_id']}, '{$item['name']}', {$item['quantity']}, 
                                   {$item['price']}, {$item['subtotal']})";

                if (!mysqli_query($conn, $insertItemQuery)) {
                    throw new Exception("Order item insertion failed: " . mysqli_error($conn));
                }

                $updateStockQuery = "UPDATE products SET stock = stock - {$item['quantity']} 
                                    WHERE id = {$item['product_id']}";

                if (!mysqli_query($conn, $updateStockQuery)) {
                    throw new Exception("Stock update failed: " . mysqli_error($conn));
                }
            }

            $clearCartQuery = "DELETE FROM cart WHERE user_id = $userId";
            mysqli_query($conn, $clearCartQuery);

            mysqli_commit($conn);

            $_SESSION['cart_count'] = 0;

            redirect('order-success.php?order_id=' . $orderId);
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors[] = "Error placing order: " . $e->getMessage() . " Please run update-database.php first if columns are missing.";
        }
    }
}

$pageTitle = "Checkout";
include 'includes/header.php';
?>

<style>
.payment-methods {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin: 1.5rem 0;
}
.payment-method {
    border: 2px solid #e0e0e0;
    padding: 1.5rem 1rem;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}
.payment-method:hover {
    border-color: #3498db;
    background: #f8f9fa;
    transform: translateY(-2px);
}
.payment-method.active {
    border-color: #3498db;
    background: #e8f4f8;
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}
.payment-method i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #3498db;
}
.payment-method small {
    color: #666;
    font-size: 0.85rem;
}
.payment-form {
    display: none;
    margin: 1.5rem 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}
.payment-form.active {
    display: block;
}
.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    padding: 12px;
    border-radius: 6px;
    margin-top: 1rem;
}
.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 12px;
    border-radius: 6px;
}
@media (max-width: 768px) {
    .payment-methods {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<div class="container">
    <h1>Checkout</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="checkout-form">
            <h2>Shipping Information</h2>
            <form method="POST" id="checkoutForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="text" id="phone" name="phone" required
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address">Shipping Address *</label>
                    <textarea id="address" name="address" rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <h3>Payment Method</h3>
                <div class="payment-methods">
                    <div class="payment-method" onclick="selectPaymentMethod('Card')">
                        <i class="fas fa-credit-card"></i>
                        <div><strong>Card</strong></div>
                        <small>Credit/Debit</small>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('UPI')">
                        <i class="fas fa-mobile-alt"></i>
                        <div><strong>UPI</strong></div>
                        <small>Google Pay, PhonePe</small>
                    </div>
                    <div class="payment-method" onclick="selectPaymentMethod('Wallet')">
                        <i class="fas fa-wallet"></i>
                        <div><strong>Wallet</strong></div>
                        <small>Paytm, Amazon Pay</small>
                    </div>
                    <div class="payment-method active" onclick="selectPaymentMethod('COD')">
                        <i class="fas fa-money-bill-wave"></i>
                        <div><strong>COD</strong></div>
                        <small>Cash on Delivery</small>
                    </div>
                </div>

                <input type="hidden" name="payment_method" id="payment_method" value="COD" required>

                <!-- Card Payment Form -->
                <div id="cardForm" class="payment-form">
                    <div class="form-group">
                        <label><i class="fas fa-credit-card"></i> Card Number *</label>
                        <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" 
                               oninput="formatCardNumber(this)">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Card Holder Name *</label>
                        <input type="text" name="card_holder" placeholder="John Doe">
                    </div>
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Expiry Date *</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                <select name="expiry_month">
                                    <option value="">MM</option>
                                    <?php for($m=1; $m<=12; $m++): ?>
                                        <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select name="expiry_year">
                                    <option value="">YYYY</option>
                                    <?php for($y=2026; $y<=2035; $y++): ?>
                                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> CVV *</label>
                            <input type="password" name="cvv" placeholder="123" maxlength="3" pattern="[0-9]{3}">
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> We accept Visa, Mastercard, Amex, and Discover cards.</small>
                    </div>
                </div>

                <!-- UPI Payment Form -->
                <div id="upiForm" class="payment-form">
                    <div class="form-group">
                        <label><i class="fas fa-mobile-alt"></i> UPI ID *</label>
                        <input type="text" name="upi_id" placeholder="yourname@upi">
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> <strong>Test Mode:</strong> Enter any UPI ID format (e.g., test@paytm)</small>
                    </div>
                </div>

                <!-- Wallet Payment Form -->
                <div id="walletForm" class="payment-form">
                    <div class="form-group">
                        <label><i class="fas fa-wallet"></i> Wallet Type *</label>
                        <select name="wallet_type">
                            <option value="">Select Wallet</option>
                            <option value="Paytm">Paytm</option>
                            <option value="PhonePe">PhonePe</option>
                            <option value="Google Pay">Google Pay</option>
                            <option value="Amazon Pay">Amazon Pay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Mobile Number *</label>
                        <input type="tel" name="wallet_mobile" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}">
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> <strong>Test Mode:</strong> Enter any 10-digit mobile number</small>
                    </div>
                </div>

                <!-- COD Notice -->
                <div id="codForm" class="payment-form active">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i> You will pay in cash when the order is delivered to your address.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-large">Place Order</button>
            </form>
        </div>

        <div class="checkout-summary">
            <h2>Order Summary</h2>

            <div class="order-items-list">
                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <span class="item-name"><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span class="item-price"><?php echo formatPrice($item['subtotal']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-totals">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>Included</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectPaymentMethod(method) {

    document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');

    document.getElementById('payment_method').value = method;

    document.querySelectorAll('.payment-form').forEach(el => el.classList.remove('active'));

    document.getElementById(method.toLowerCase() + 'Form').classList.add('active');
}

function formatCardNumber(input) {
    let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let parts = value.match(/.{1,4}/g);
    input.value = parts ? parts.join(' ') : value;
}
</script>

<?php include 'includes/footer.php'; ?>