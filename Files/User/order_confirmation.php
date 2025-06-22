<?php
include("connection.php");
session_start();

if (!isset($_GET['order_id'])) {
    die("Invalid order ID.");
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User not logged in.");
}

// Validate order belongs to the user
$order_query = "SELECT * FROM orders WHERE order_id = '$order_id' AND user_id = '$user_id'";
$order_result = mysqli_query($conn, $order_query);

if (mysqli_num_rows($order_result) == 0) {
    die("Order not found.");
}

$order_row = mysqli_fetch_assoc($order_result);

// Fetch order items (product-based)
$order_items_query = "SELECT order_items.*, products.name AS product_name, products.price 
                      FROM order_items 
                      JOIN products ON order_items.product_id = products.product_id 
                      WHERE order_items.order_id = '$order_id'";
$order_items_result = mysqli_query($conn, $order_items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="/Book_Store/Style/order_confirmation.css">
</head>
<body>
    <div class="confirmation-container">
        <h2>Order Confirmation</h2>

        <h3>Order ID: <?php echo $order_row['order_id']; ?></h3>
        <p><strong>Total Amount:</strong> Rs. <?php echo $order_row['total_amount']; ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($order_row['status']); ?></p>
        <p><strong>Order Date:</strong> <?php echo $order_row['order_date']; ?></p>

        <h3>Order Details</h3>
        <table border="1">
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
            <?php
            $total_price = 0;
            while ($item = mysqli_fetch_assoc($order_items_result)) {
                $item_total = $item['quantity'] * $item['unit_price'];
                $total_price += $item_total;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($item['product_name']) . "</td>";
                echo "<td>" . $item['quantity'] . "</td>";
                echo "<td>Rs. " . $item['unit_price'] . "</td>";
                echo "<td>Rs. " . $item_total . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h3>Total Order Price: Rs. <?php echo $total_price; ?></h3>

        <p>Your order has been placed successfully. Thank you for shopping with us!</p>

        <a href="home.php">Go back to Home</a>
    </div>
</body>
</html>
