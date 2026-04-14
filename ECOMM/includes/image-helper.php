<?php

function getProductImage($imageName, $productName) {
    if ($imageName && file_exists(UPLOAD_PATH . $imageName)) {
        return UPLOAD_URL . $imageName;
    }

    $encodedName = urlencode($productName);
    return "https://via.placeholder.com/400x400/667eea/ffffff?text=" . $encodedName;
}

function getCategoryImage($imageName, $categoryName) {
    if ($imageName && file_exists(UPLOAD_PATH . $imageName)) {
        return UPLOAD_URL . $imageName;
    }

    $encodedName = urlencode($categoryName);
    return "https://via.placeholder.com/400x300/764ba2/ffffff?text=" . $encodedName;
}
?>
