<?php
require_once 'includes/config.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    }

    $checkQuery = "SELECT id FROM users WHERE email = '$email' AND id != $userId";
    $checkResult = mysqli_query($conn, $checkQuery);
    if (mysqli_num_rows($checkResult) > 0) {
        $errors[] = "Email already in use by another account";
    }

    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = "Current password is required to change password";
        } else {

            $userQuery = "SELECT password FROM users WHERE id = $userId";
            $userResult = mysqli_query($conn, $userQuery);
            $userData = mysqli_fetch_assoc($userResult);

            if (!password_verify($current_password, $userData['password'])) {
                $errors[] = "Current password is incorrect";
            }
        }

        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
    }

    if (empty($errors)) {

        if (!empty($new_password)) {
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', 
                           address = '$address', password = '$hashedPassword' WHERE id = $userId";
        } else {
            $updateQuery = "UPDATE users SET name = '$name', email = '$email', phone = '$phone', 
                           address = '$address' WHERE id = $userId";
        }

        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            showMessage("Profile updated successfully", "success");
            redirect('profile.php');
        } else {
            $errors[] = "Failed to update profile";
        }
    }
}

$userQuery = "SELECT * FROM users WHERE id = $userId";
$userResult = mysqli_query($conn, $userQuery);
$user = mysqli_fetch_assoc($userResult);

$statsQuery = "SELECT COUNT(*) as total_orders, 
               COALESCE(SUM(total_amount), 0) as total_spent 
               FROM orders WHERE user_id = $userId";
$statsResult = mysqli_query($conn, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

$pageTitle = "My Profile";
include 'includes/header.php';
?>

<div class="container">
    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $stats['total_orders']; ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo formatPrice($stats['total_spent']); ?></span>
                        <span class="stat-label">Total Spent</span>
                    </div>
                </div>
            </div>

            <div class="profile-menu">
                <a href="profile.php" class="active">Profile Settings</a>
                <a href="orders.php">My Orders</a>
                <a href="cart.php">Shopping Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="profile-content">
            <h1>Profile Settings</h1>

            <?php displayMessage(); ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="profile.php" class="profile-form">
                <div class="form-section">
                    <h3>Personal Information</h3>

                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Change Password</h3>
                    <p class="form-hint">Leave blank if you don't want to change your password</p>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="6">
                        <small>Minimum 6 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
