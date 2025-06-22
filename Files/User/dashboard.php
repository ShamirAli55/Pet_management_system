<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch pets
$pets_query = "SELECT * FROM pets WHERE added_by = $user_id";
$pets_result = mysqli_query($conn, $pets_query);

// Fetch appointments
$appointments_query = "SELECT a.*, v.name AS vet_name, s.service_name FROM appointments a LEFT JOIN veterinarians v ON a.vet_id = v.vet_id LEFT JOIN vet_services s ON a.service_id = s.service_id WHERE a.user_id = $user_id ORDER BY a.appointment_date DESC";
$appointments_result = mysqli_query($conn, $appointments_query);

// Fetch orders
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

// Fetch order items (purchases)
$purchases_query = "SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.product_id JOIN orders o ON oi.order_id = o.order_id WHERE o.user_id = $user_id ORDER BY o.order_date DESC";
$purchases_result = mysqli_query($conn, $purchases_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://kit.fontawesome.com/647670bc4e.js" crossorigin="anonymous"></script>
  <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
:root {
    --header: rgb(54, 52, 52);
    --primary: #0077cc;
    --hover: #005fa3;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background-color: #f4f4f4;
    line-height: 1.6;
}

.header_container {
    width: 100%;
    background-color: var(--header);
    border-radius: 0.4rem;
    padding: 0 2rem;
    margin-bottom: 2rem;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0;
    flex-wrap: wrap;
}

.left-group {
    display: flex;
    align-items: center;
    gap: 15px;
}

.menu-toggle {
    display: none;
    font-size: 22px;
    color: white;
    cursor: pointer;
}

.name {
    color: white;
    font-size: 1.6rem;
    font-weight: bold;
}

#nav_bar {
    display: flex;
    gap: 20px;
    align-items: center;
}

#nav_bar a {
    text-decoration: none;
    color: white;
    font-weight: 600;
    transition: color 0.3s;
    padding: 8px 0;
}

#nav_bar a:hover {
    color: var(--primary);
}

.header-right {
    display: flex;
    gap: 10px;
    align-items: center;
}

.auth-btn, .butns {
    background-color: #333;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s;
    font-size: 16px;
}

.auth-btn:hover, .butns:hover {
    background-color: #555;
}

@media (max-width: 768px) {
    .menu-toggle {
        display: block;
    }

    #nav_bar {
        display: none;
        flex-direction: column;
        width: 100%;
        background-color: var(--header);
        padding: 10px 0;
        position: absolute;
        top: 60px;
        left: 0;
        z-index: 100;
    }

    #nav_bar.show {
        display: flex;
    }

    #nav_bar a {
        padding: 10px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
    }

    .header-right {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0;
    }

    .left-group {
        display: flex;
        align-items: center;
        gap: 15px;
    }
}

.container {
    max-width: 1100px;
    margin: auto;
    padding: 2rem 1rem;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #333;
    border-bottom: 2px solid #0077cc;
    padding-bottom: 5px;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.section {
    margin-bottom: 2.5rem;
    padding: 1.5rem;
    background: #fafafa;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.item {
    margin-bottom: 12px;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    background-color: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.item:last-child {
    border-bottom: none;
}


.back-btn:hover {
    background-color: #005fa3;
}

.auth-btn:focus, .back-btn:focus, button:focus {
    outline: 2px dashed var(--primary);
    outline-offset: 3px;
}

.auth-btn:active, .back-btn:active {
    background-color: #004a80;
    transform: scale(0.98);
}
.cart-button-wrapper {
  position: relative;
  display: inline-block;
}

.auth-btn {
  position: relative;
  font-size: 20px;
  background: none;
  border: none;
  cursor: pointer;
}

.cart-badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background-color: red;
  color: white;
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 50%;
  font-weight: bold;
  line-height: 1;
}

</style>
</head>
<body>

<div class="header_container">
  <div class="header">
    <div class="name">PetSphere</div>
    <nav id="nav_bar">
      <a href="home.php">Home</a>
      <a href="pet_supplies.php" class="active">Pet Supplies</a>
      <a href="appointments.php">Appointments</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="veterinarian_services.php">Veterinarian Services</a>
      <a href="pets.php">My Pets</a>
    </nav>
    <div class="header-right">
    <?php
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $user_id");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>

<div class="cart-button-wrapper">
  <button class="auth-btn" onclick="window.location.href='cart.php'">
    <i class="fa-solid fa-cart-shopping"></i>
    <?php if ($cart_count > 0): ?>
      <span class="cart-badge"><?= $cart_count ?></span>
    <?php endif; ?>
  </button>
  <button class="auth-btn" onclick="window.location.href='profile.php'"><i class="fa-regular fa-user"></i></button>
</div>
    <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    </div>
  </div>
</div>


  <div class="container">
    <h1>Welcome to Your Dashboard</h1>

    <div class="section">
      <h2>Your Pets</h2>
      <?php if (mysqli_num_rows($pets_result) > 0): while($pet = mysqli_fetch_assoc($pets_result)): ?>
        <div class="item">
          <strong><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['type']) ?>)</strong><br>
          Breed: <?= htmlspecialchars($pet['breed']) ?> | Age: <?= $pet['age'] ?> | Gender: <?= $pet['gender'] ?>
        </div>
      <?php endwhile; else: ?>
        <p>No pets added.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>Your Appointments</h2>
      <?php if (mysqli_num_rows($appointments_result) > 0): while($app = mysqli_fetch_assoc($appointments_result)): ?>
        <div class="item">
          <strong><?= htmlspecialchars($app['service_name']) ?></strong> with <?= htmlspecialchars($app['vet_name']) ?><br>
          <?= htmlspecialchars($app['appointment_date']) ?> at <?= date('g:i A', strtotime($app['appointment_time'])) ?>
        </div>
      <?php endwhile; else: ?>
        <p>No appointments found.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>Your Orders</h2>
      <?php if (mysqli_num_rows($orders_result) > 0): while($order = mysqli_fetch_assoc($orders_result)): ?>
        <div class="item">
          Order #<?= $order['order_id'] ?> | Date: <?= $order['order_date'] ?> | Status: <?= $order['status'] ?> | Total: Rs.<?= $order['total_amount'] ?>
        </div>
      <?php endwhile; else: ?>
        <p>No orders found.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>Your Purchases</h2>
      <?php if (mysqli_num_rows($purchases_result) > 0): while($purchase = mysqli_fetch_assoc($purchases_result)): ?>
        <div class="item">
          <?= htmlspecialchars($purchase['name']) ?> | Quantity: <?= $purchase['quantity'] ?> | Unit Price: $<?= $purchase['unit_price'] ?>
        </div>
      <?php endwhile; else: ?>
        <p>No purchases recorded.</p>
      <?php endif; ?>
    </div>
  </div>
  <script>
document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.querySelector(".menu-toggle");
    const navBar = document.getElementById("nav_bar");

    toggle?.addEventListener("click", () => {
        navBar?.classList.toggle("show");
    });
});
</script>
</body>
</html>
