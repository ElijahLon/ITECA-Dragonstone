<?php
session_start();

// Get the product ID to remove
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id > 0 && isset($_SESSION['cart'][$id])) {
    // Remove the item from the cart
    unset($_SESSION['cart'][$id]);
}

// If AJAX request, return success
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    echo 'OK';
    exit;
}

// Redirect back to the cart page
header('Location: cart.php');
exit;
?>
