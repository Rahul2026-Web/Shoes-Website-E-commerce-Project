<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Product ID required']);
    exit();
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $query);

if ($product = mysqli_fetch_assoc($result)) {
    echo json_encode($product);
} else {
    echo json_encode(['error' => 'Product not found']);
}
