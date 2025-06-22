<?php
include("connection.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$query = "SELECT orders.order_id, orders.order_date, products.name, products.new_price, order_items.quantity
          FROM orders
          JOIN order_items ON orders.order_id = order_items.order_id
          JOIN products ON order_items.product_id = products.product_id
          WHERE orders.user_id = $user_id
          ORDER BY orders.order_date DESC";

$result = mysqli_query($conn, $query);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['order_id']]['date'] = $row['order_date'];
    $orders[$row['order_id']]['items'][] = [
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="/SRE/Pet_managment_system/Style/cart.css">
    <style>
        .order-container {
            margin: 30px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
            width: 90%;
            max-width: 800px;
        }
        .order-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

<div style="padding: 20px 30px;">
    <a href="home.php" style="
        padding: 8px 16px;
        background-color: rgb(54, 52, 52);
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
        font-size: 0.95rem;
    ">&larr; Back to Cart</a>
</div>

<h2 style="text-align:center">Your Order History</h2>

<?php if (empty($orders)): ?>
    <p style="text-align:center; font-size:1.1rem;">You have no past orders.</p>
<?php else: ?>
    <?php foreach ($orders as $order_id => $order): ?>
        <div class="order-container">
            <div class="order-title">Order #<?= $order_id ?> - Date: <?= $order['date'] ?></div>
            <table>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
                <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>Rs. <?= $item['price'] ?></td>
                    <td>Rs. <?= $item['quantity'] * $item['price'] ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
