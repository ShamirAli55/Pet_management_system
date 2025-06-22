<?php
include("connection.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['cart_id'])) {
    $cart_id = (int) $_GET['cart_id'];

    // Get product_id and quantity before deleting
    $query = "SELECT product_id, quantity FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $product_id = $row['product_id'];
        $quantity = $row['quantity'];

        // Restore the stock
        $update_stock = "UPDATE products SET stock = stock + $quantity WHERE product_id = $product_id";
        mysqli_query($conn, $update_stock);
    }

    // Remove from cart
    $delete = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    mysqli_query($conn, $delete);
}

header("Location: cart.php");
exit;
