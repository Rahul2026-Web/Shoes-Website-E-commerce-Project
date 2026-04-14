<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="The Shoe Vault - Premium footwear collection for every occasion. Shop men's, women's, sports, casual and formal shoes.">
    <meta name="keywords" content="shoes, footwear, sneakers, boots, sandals, online shoe store">
    <meta name="author" content="The Shoe Vault">
    <title><?php echo isset($pageTitle) ? 'The Shoe Vault - ' . $pageTitle : 'The Shoe Vault | Premium Footwear Collection'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo BASE_URL; ?>index.php" class="logo">
                <h1><span class="logo-the">THE</span> <span class="logo-shoe">SHOE</span> <span class="logo-vault">VAULT</span></h1>
            </a>
            <ul class="nav-menu">
                <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
                <?php if (!isLoggedIn() || !isAdmin()): ?>
                    <li><a href="<?php echo BASE_URL; ?>products.php">Products</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact.php">Contact</a></li>
                <?php endif; ?>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/categories.php">Categories</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/products.php">Products</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/orders.php">Orders</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/customers.php">Customers</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/reports.php">Reports</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/contact-messages.php">Messages</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>cart.php">Cart
                                <?php
                                if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] > 0): ?>
                                    <span class="cart-badge"><?php echo $_SESSION['cart_count']; ?></span>
                                <?php endif; ?>
                            </a></li>
                        <li><a href="<?php echo BASE_URL; ?>orders.php">My Orders</a></li>
                        <li><a href="<?php echo BASE_URL; ?>profile.php">Profile</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
    <main class="main-content">