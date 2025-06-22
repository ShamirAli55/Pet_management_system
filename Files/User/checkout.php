<?php
include("connection.php");
session_start(); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT cart.cart_id, cart.quantity, products.price, products.name AS title, products.image_url
          FROM cart
          JOIN products ON cart.product_id = products.product_id
          WHERE cart.user_id = '$user_id'";

$result = mysqli_query($conn, $query);

$total_price = 0;
$items_in_cart = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $total = $row['quantity'] * $row['price'];
    $total_price += $total;
    $items_in_cart++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="/SRE/Pet_managment_system/Style/cart.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        h2 {
            text-align: center;
        }
        .cart-table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .total-price {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            max-width: 500px;
            margin: auto;
            background: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .warning {
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

    <h2>Checkout</h2>

    <?php if ($items_in_cart == 0): ?>
        <p class="warning">Your cart is empty. Please add items before proceeding.</p>
    <?php else: ?>

    <div class="cart-table-container">
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
            <?php
            mysqli_data_seek($result, 0);
            while ($row = mysqli_fetch_assoc($result)) {
                $total = $row['quantity'] * $row['price'];
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "<td>Rs. " . $row['price'] . "</td>";
                echo "<td>Rs. " . $total . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>

    <div class="total-price">
        Total: Rs. <?php echo $total_price; ?>
    </div>

    <form action="place_order.php" method="POST" onsubmit="return confirmCheckout();">
        <h3>Billing Information</h3>
        <label for="address">Shipping Address:</label>
        <input type="text" name="address" required placeholder="Enter your shipping address">

        <label for="city">City:</label>
        <input type="text" name="city" required placeholder="Enter your city">

        <label for="postal_code">Postal Code:</label>
        <input type="text" name="postal_code" required placeholder="Enter your postal code">

        <h3>Payment Method</h3>
        <label for="payment">Select Payment Method:</label>
        <select name="payment" required>
            <option value="credit_card">Credit Card</option>
            <option value="paypal">PayPal</option>
            <option value="cash_on_delivery">Cash on Delivery</option>
        </select>

        <button type="submit" name="place_order">Place Order</button>
    </form>
    <?php endif; ?>

    <script>
        function confirmCheckout() {
            const total = <?= $items_in_cart ?>;
            if (total <= 0)
             {
                alert("You cannot proceed with an empty cart!");
                return false;
            }
            return true;
        }
    </script>

</body>
</html>
