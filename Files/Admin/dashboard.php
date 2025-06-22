<?php
include("../User/connection.php");
session_start();
$user = $_SESSION['user_id'] ?? 0;

// Metrics
$user_query = mysqli_query($conn, "SELECT COUNT(*) as total_users FROM users");
$total_users = mysqli_fetch_assoc($user_query)['total_users'];

$product_query = mysqli_query($conn, "SELECT COUNT(*) as total_products FROM products");
$total_products = mysqli_fetch_assoc($product_query)['total_products'];

$pet_query = mysqli_query($conn, "SELECT COUNT(*) as total_pets FROM pets");
$total_pets = mysqli_fetch_assoc($pet_query)['total_pets'];

$order_query = mysqli_query($conn, "SELECT COUNT(*) as total_orders FROM orders");
$total_orders = mysqli_fetch_assoc($order_query)['total_orders'];

$appointment_query = mysqli_query($conn, "SELECT COUNT(*) as total_appointments FROM appointments");
$total_appointments = mysqli_fetch_assoc($appointment_query)['total_appointments'];

$revenue_query = mysqli_query($conn, "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'paid'");
$total_revenue = mysqli_fetch_assoc($revenue_query)['total_revenue'] ?? 0.00;

// Latest 5 Orders
$order_list_query = mysqli_query($conn, "
  SELECT o.order_id, u.username AS customer_name, o.order_date, o.status, o.total_amount 
  FROM orders o
  JOIN users u ON o.user_id = u.user_id
  ORDER BY o.order_date DESC
  LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');
    :root {
      --header: rgb(54, 52, 52);
      --primary: #0077cc;
      --hover: #005fa3;
      --bg: #f4f4f4;
      --card-bg: #fff;
      --card-shadow: rgba(0, 0, 0, 0.1);
    }
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background-color: var(--bg);
      line-height: 1.6;
    }
    .header_container {
      width: 100%;
      background-color: var(--header);
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
    .auth-btn {
      background-color: #333;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 16px;
    }
    .auth-btn:hover {
      background-color: #555;
    }
    .menu-toggle {
      display: none;
      font-size: 22px;
      color: white !important;
      cursor: pointer;
    }
    @media (max-width: 768px) {
      .menu-toggle { display: block; }
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
        display: flex !important;
      }
    }
    .header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

    .dashboard {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    h1 {
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: #333;
    }
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
    }
    .card {
      background-color: var(--card-bg);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 10px var(--card-shadow);
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-4px);
    }
    .card h3 {
      font-size: 1.2rem;
      margin-bottom: 10px;
      color: var(--primary);
    }
    .card p {
      font-size: 1.5rem;
      font-weight: bold;
      color: #333;
    }

    /* Latest Orders Styles */
    .latest-orders {
      margin-top: 40px;
    }
    .latest-orders h2 {
      font-size: 1.8rem;
      margin-bottom: 15px;
      color: #333;
    }
    .orders-table {
      width: 100%;
      border-collapse: collapse;
      background-color: var(--card-bg);
      border-radius: 10px;
      box-shadow: 0 2px 10px var(--card-shadow);
      overflow: hidden;
    }
    .orders-table th,
    .orders-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #eee;
    }
    .orders-table th {
      background-color: var(--header);
      color: white;
    }
    .orders-table tr:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>
<body>
  <div class="header_container">
    <div class="header">
      <div class="name">PetSphere</div>
      <nav id="nav_bar">
        <a href="dashboard.php">Dashboard</a>
        <a href="user_management.php">User Management</a>
        <a href="appointments.php">Appointments</a>
        <a href="view_products.php">Manage Products</a>
      </nav>
      <div class="header-right">
        <button class="auth-btn" onclick="window.location.href='profile.php'">
          <i class="fa-regular fa-user"></i>
        </button>
        <div class="menu-toggle" id="menu-toggle">&#9776;</div>
      </div>
    </div>
  </div>

  <div class="dashboard">
    <h1>Admin Dashboard</h1>
    <div class="cards">
      <div class="card"><h3>Total Users</h3><p><?php echo $total_users; ?></p></div>
      <div class="card"><h3>Appointments</h3><p><?php echo $total_appointments; ?></p></div>
      <div class="card"><h3>Total Orders</h3><p><?php echo $total_orders; ?></p></div>
      <div class="card"><h3>Total Products</h3><p><?php echo $total_products; ?></p></div>
      <div class="card"><h3>Registered Pets</h3><p><?php echo $total_pets; ?></p></div>
      <div class="card"><h3>Total Revenue</h3><p>Rs. <?php echo number_format($total_revenue,2); ?></p></div>
    </div>

    <!-- Latest Orders Section -->
    <div class="latest-orders">
      <h2>Latest Orders</h2>
      <table class="orders-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Date</th>
            <th>Status</th>
            <th>Amount (Rs)</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($order = mysqli_fetch_assoc($order_list_query)): ?>
          <tr>
            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
            <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
            <td><?php echo ucfirst($order['status']); ?></td>
            <td><?php echo number_format($order['total_amount'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    const toggle = document.getElementById('menu-toggle');
    const nav = document.getElementById('nav_bar');
    toggle.addEventListener('click', () => {
      nav.classList.toggle('show');
    });
  </script>
</body>
</html>
