<?php
include("connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment']);

    $cart_query = "SELECT cart.product_id, products.price, cart.quantity
                   FROM cart
                   JOIN products ON cart.product_id = products.product_id
                   WHERE cart.user_id = '$user_id'";

    $cart_result = mysqli_query($conn, $cart_query);

    $items = [];
    $total_amount = 0;

    while ($row = mysqli_fetch_assoc($cart_result)) {
        $items[] = $row;
        $total_amount += $row['price'] * $row['quantity'];
    }

    if (empty($items)) {
        echo "<script>alert('Your cart is empty. Please add items before proceeding.'); window.location.href = 'cart.php';</script>";
        exit;
    }

    // Insert into orders
    $insert_order = "INSERT INTO orders (user_id, total_amount) VALUES ('$user_id', '$total_amount')";
    mysqli_query($conn, $insert_order);
    $order_id = mysqli_insert_id($conn);

    // Insert into order_items
    foreach ($items as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                             VALUES ('$order_id', '$product_id', '$quantity', '$price')");
    }

    // Insert into payments
    $insert_payment = "INSERT INTO payments (order_id, payment_method, amount, status)
                       VALUES ('$order_id', '$payment_method', '$total_amount', 'paid')";
    mysqli_query($conn, $insert_payment);

    // Clear cart
    mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

    echo "<script>alert('Order placed successfully!'); window.location.href = 'pet_supplies.php';</script>";
}
?>