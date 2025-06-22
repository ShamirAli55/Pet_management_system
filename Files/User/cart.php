<?php
include("connection.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// ✅ Store the original referring page only once
if (!isset($_SESSION['cart_referrer'])) {
    $ref = $_SERVER['HTTP_REFERER'] ?? 'home.php';
    if (!str_contains($ref, 'cart.php')) {
        $_SESSION['cart_referrer'] = $ref;
    }
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity   = (int) ($_POST['quantity'] ?? 1);

    $stock_query  = "SELECT stock FROM products WHERE product_id = $product_id";
    $stock_result = mysqli_query($conn, $stock_query);
    $stock_row    = mysqli_fetch_assoc($stock_result);
    $available_stock = (int) $stock_row['stock'];

    if ($available_stock >= $quantity) {
        $check_query  = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $update_query = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
            mysqli_query($conn, $update_query);
        } else {
            $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
            mysqli_query($conn, $insert_query);
        }

        $new_stock     = $available_stock - $quantity;
        $stock_update  = "UPDATE products SET stock = $new_stock WHERE product_id = $product_id";
        mysqli_query($conn, $stock_update);
    }

    $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'pet_supplies.php';
    header("Location: $redirect_url");
    exit;
}

// Handle quantity updates
if (isset($_GET['action']) && isset($_GET['cart_id'])) {
    $cart_id = (int) $_GET['cart_id'];
    $action  = $_GET['action'];

    $cart_query  = "SELECT quantity, product_id FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    $cart_result = mysqli_query($conn, $cart_query);
    $cart_row    = mysqli_fetch_assoc($cart_result);

    if ($cart_row) {
        $current_quantity = $cart_row['quantity'];
        $product_id       = $cart_row['product_id'];

        if ($action === 'increase') {
            $stock_result = mysqli_query($conn, "SELECT stock FROM products WHERE product_id = $product_id");
            $stock        = mysqli_fetch_assoc($stock_result)['stock'];

            if ($stock > 0) {
                mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE cart_id = $cart_id");
                mysqli_query($conn, "UPDATE products SET stock = stock - 1 WHERE product_id = $product_id");
            }
        } elseif ($action === 'decrease' && $current_quantity > 1) {
            mysqli_query($conn, "UPDATE cart SET quantity = quantity - 1 WHERE cart_id = $cart_id");
            mysqli_query($conn, "UPDATE products SET stock = stock + 1 WHERE product_id = $product_id");
        }
    }
}

$query  = "SELECT cart.cart_id, cart.quantity, products.name, products.new_price, products.image_url
           FROM cart
           JOIN products ON cart.product_id = products.product_id
           WHERE cart.user_id = $user_id";

$result = mysqli_query($conn, $query);

$cart_count  = 0;
$count_query = "SELECT SUM(quantity) AS total_items FROM cart WHERE user_id = $user_id";
$count_result = mysqli_query($conn, $count_query);
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}

// ✅ Use saved referrer as back link
$previous_url = $_SESSION['cart_referrer'] ?? 'home.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart</title>
  <link rel="stylesheet" href="./Style/cart.css" />
</head>
<body>

<div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 30px;">
  <a href="<?= htmlspecialchars($previous_url) ?>" style="
    padding: 8px 16px;
    background-color: rgb(54, 52, 52);
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: background 0.3s ease;
  " onmouseover="this.style.backgroundColor='#005fa3'" onmouseout="this.style.backgroundColor='rgb(54, 52, 52)'">
    &larr; Back
  </a>

  <h2 style="margin:0;margin-left:60px;text-align:center;">Your Cart</h2>

  <a href="order_history.php" style="
    padding: 8px 16px;
    background-color: darkgreen;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: background 0.3s ease;
  " onmouseover="this.style.backgroundColor='green'" onmouseout="this.style.backgroundColor='darkgreen'">
    🧾 View Order History
  </a>
</div>

<div style="padding: 10px 30px; font-size: 1.1rem; text-align: center;">
  🛒 Items in cart: <strong><?= $cart_count ?></strong>
</div>

<div class="cart-table-container">
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th style="text-align: center;">Quantity</th>
        <th style="text-align: center;">Price</th>
        <th style="text-align: center;">Total</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $grand_total = 0;
      while ($row = mysqli_fetch_assoc($result)) {
          $total = $row['quantity'] * $row['new_price'];
          $grand_total += $total;
          echo "<tr>";
          echo "<td>{$row['name']}</td>";
          echo "<td style='text-align:center;'>
                  <div class='cart-item-wrapper'>
                      <a href='cart.php?action=increase&cart_id={$row['cart_id']}'>↑</a>
                      <div class='quantity-value'>{$row['quantity']}</div>
                      <a href='cart.php?action=decrease&cart_id={$row['cart_id']}'>↓</a>
                  </div>
                </td>";
          echo "<td style='text-align:center;'>Rs. " . number_format($row['new_price'], 2) . "</td>";
          echo "<td style='text-align:center;'>Rs. " . number_format($total, 2) . "</td>";
          echo "<td><a href='remove_from_cart.php?cart_id={$row['cart_id']}'>Remove</a></td>";
          echo "</tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<div class="total-price">
  <h3>Total: Rs. <?= number_format($grand_total, 2) ?></h3>
</div>

<form action="checkout.php" method="POST" onsubmit="return validateCheckout()">
  <button type="submit">Proceed to Checkout</button>
</form>

<script>
function validateCheckout() {
  const cartCount = <?= $cart_count ?>;
  if (cartCount === 0) {
    alert("Your cart is empty. Please add items before proceeding to checkout.");
    return false;
  }
  return true;
}
</script>

</body>
</html>
