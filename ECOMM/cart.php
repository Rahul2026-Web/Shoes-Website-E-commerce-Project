<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'add') {
            $productId = (int)$_POST['product_id'];
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

            $productQuery = "SELECT stock FROM products WHERE id = $productId";
            $productResult = mysqli_query($conn, $productQuery);
            $product = mysqli_fetch_assoc($productResult);

            if ($product && $product['stock'] >= $quantity) {

                $checkQuery = "SELECT * FROM cart WHERE user_id = $userId AND product_id = $productId";
                $checkResult = mysqli_query($conn, $checkQuery);

                if (mysqli_num_rows($checkResult) > 0) {

                    $existing = mysqli_fetch_assoc($checkResult);
                    $newQuantity = $existing['quantity'] + $quantity;
                    if ($newQuantity <= $product['stock']) {
                        $updateQuery = "UPDATE cart SET quantity = $newQuantity WHERE id = {$existing['id']}";
                        mysqli_query($conn, $updateQuery);
                        echo json_encode(['success' => true, 'message' => 'Cart updated']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Not enough stock']);
                    }
                } else {

                    $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($userId, $productId, $quantity)";
                    if (mysqli_query($conn, $insertQuery)) {
                        echo json_encode(['success' => true, 'message' => 'Added to cart']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
                    }
                }

                $countQuery = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $userId";
                $countResult = mysqli_query($conn, $countQuery);
                $countData = mysqli_fetch_assoc($countResult);
                $_SESSION['cart_count'] = $countData['count'] ?? 0;
            } else {
                echo json_encode(['success' => false, 'message' => 'Product not available']);
            }
            exit;
        }

        if ($action === 'update') {
            $cartId = (int)$_POST['cart_id'];
            $quantity = (int)$_POST['quantity'];

            if ($quantity > 0) {

                $checkQuery = "SELECT p.stock FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.id = $cartId AND c.user_id = $userId";
                $checkResult = mysqli_query($conn, $checkQuery);
                $product = mysqli_fetch_assoc($checkResult);

                if ($product && $quantity <= $product['stock']) {
                    $updateQuery = "UPDATE cart SET quantity = $quantity WHERE id = $cartId AND user_id = $userId";
                    mysqli_query($conn, $updateQuery);
                    showMessage("Cart updated", "success");
                } else {
                    showMessage("Not enough stock", "danger");
                }
            }
            redirect('cart.php');
        }

        if ($action === 'remove') {
            $cartId = (int)$_POST['cart_id'];
            $deleteQuery = "DELETE FROM cart WHERE id = $cartId AND user_id = $userId";
            mysqli_query($conn, $deleteQuery);
            showMessage("Item removed from cart", "success");

            $countQuery = "SELECT SUM(quantity) as count FROM cart WHERE user_id = $userId";
            $countResult = mysqli_query($conn, $countQuery);
            $countData = mysqli_fetch_assoc($countResult);
            $_SESSION['cart_count'] = $countData['count'] ?? 0;

            redirect('cart.php');
        }
    }
}

$cartQuery = "SELECT c.*, p.name, p.price, p.stock, p.image 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $userId";
$cartResult = mysqli_query($conn, $cartQuery);

$total = 0;
$cartItems = [];
while ($item = mysqli_fetch_assoc($cartResult)) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cartItems[] = $item;
}

$pageTitle = "Shopping Cart";
include 'includes/header.php';
?>

<div class="container">
    <h1>Shopping Cart</h1>

    <?php displayMessage(); ?>

    <?php if (count($cartItems) > 0): ?>
        <div class="cart-layout">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="<?php echo getProductImage($item['image'], $item['name']); ?>"
                                alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="cart-item-price"><?php echo formatPrice($item['price']); ?> each</p>
                            <p class="cart-item-stock">Stock available: <?php echo $item['stock']; ?></p>
                        </div>
                        <div class="cart-item-quantity">
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>"
                                    min="1" max="<?php echo $item['stock']; ?>"
                                    onchange="this.form.submit()">
                            </form>
                        </div>
                        <div class="cart-item-subtotal">
                            <p><?php echo formatPrice($item['subtotal']); ?></p>
                        </div>
                        <div class="cart-item-remove">
                            <form method="POST">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-remove" onclick="return confirm('Remove this item?')">×</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span><?php echo formatPrice($total); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                <a href="products.php" class="btn btn-secondary btn-block">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <h2>Your cart is empty</h2>
            <p>Add some products to get started!</p>
            <a href="products.php" class="btn btn-primary">Browse Products</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>