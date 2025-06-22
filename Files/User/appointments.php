<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

date_default_timezone_set('Asia/Karachi');
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Update completed appointments
$update_query = "
    UPDATE appointments SET status = 'completed' 
    WHERE status = 'upcoming' AND 
         (appointment_date < '$current_date' 
         OR (appointment_date = '$current_date' AND appointment_time < '$current_time'))";
mysqli_query($conn, $update_query);

$error = "";

// Cancel appointment
if (isset($_GET['cancel'])) {
    $cancel_id = (int)$_GET['cancel'];
    mysqli_query($conn, "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = $cancel_id AND user_id = $user_id");
}

// Reschedule appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reschedule_id'])) {
    $id = (int)$_POST['reschedule_id'];
    $new_date = mysqli_real_escape_string($conn, $_POST['new_date']);
    $new_time = mysqli_real_escape_string($conn, $_POST['new_time']);

    $conflict = mysqli_query($conn, "SELECT * FROM appointments 
        WHERE vet_id = (SELECT vet_id FROM appointments WHERE appointment_id = $id)
        AND appointment_date = '$new_date' AND appointment_time = '$new_time'
        AND status NOT IN ('cancelled', 'completed') AND appointment_id != $id");

    if (mysqli_num_rows($conflict) > 0) {
        $error = "This slot is already booked!";
    } else {
        mysqli_query($conn, "UPDATE appointments SET appointment_date = '$new_date', appointment_time = '$new_time' 
        WHERE appointment_id = $id AND user_id = $user_id");
    }
}

// Book new appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vet_id'], $_POST['service_id'], $_POST['appointment_date'], $_POST['appointment_time']) && !isset($_POST['reschedule_id'])) {
    $vet_id = (int)$_POST['vet_id'];
    $service_id = (int)$_POST['service_id'];
    $appointment_date = mysqli_real_escape_string($conn, $_POST['appointment_date']);
    $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    $check = "SELECT * FROM appointments 
              WHERE vet_id = $vet_id AND appointment_date = '$appointment_date' 
              AND appointment_time = '$appointment_time' 
              AND status NOT IN ('cancelled', 'completed')";
    $alreadyBooked = mysqli_query($conn, $check);

    if (mysqli_num_rows($alreadyBooked) > 0) {
        $error = "This vet is already booked at that time.";
    } else {
        mysqli_query($conn, "INSERT INTO appointments (user_id, vet_id, service_id, appointment_date, appointment_time, notes, status) 
            VALUES ($user_id, $vet_id, $service_id, '$appointment_date', '$appointment_time', '$notes', 'confirmed')");
    }
}

// Filter
$status_filter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$filter_sql = $status_filter ? " AND a.status = '$status_filter'" : "";

$query = "SELECT a.*, v.name AS vet_name, s.service_name, s.price, s.duration_minutes, v.location 
          FROM appointments a
          LEFT JOIN veterinarians v ON a.vet_id = v.vet_id
          LEFT JOIN vet_services s ON a.service_id = s.service_id
          WHERE a.user_id = $user_id $filter_sql
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $query);

// Cart count
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $user_id");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Appointments</title>
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
    .auth-btn {
      background-color: #333;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
      font-size: 20px;
    }
    .auth-btn:hover {
      background-color: #555;
    }
    .menu-toggle {
      display: none;
      font-size: 22px;
      color: white;
      cursor: pointer;
      background-color: #333;
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
        display: flex !important;
      }
    }
    .cart-button-wrapper {
      position: relative;
      display: inline-block;
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

    .container {
      max-width: 900px;
      margin: 30px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .appointment-card {
      background: #fafafa;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .cancel-btn, .reschedule-btn {
      padding: 8px 16px;
      border-radius: 5px;
      color: #fff;
      text-decoration: none;
      margin-top: 10px;
      display: inline-block;
    }

    .cancel-btn { background-color: #e74c3c; }
    .reschedule-btn { background-color: #f39c12; }

    .status {
      font-weight: bold;
      text-transform: capitalize;
    }

    .status.confirmed { color: #3498db; }
    .status.completed { color: #27ae60; }
    .status.cancelled { color: #e74c3c; }

    .reschedule-form {
      margin-top: 10px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .reschedule-form input[type="date"],
    .reschedule-form input[type="time"] {
      padding: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    .reschedule-form button {
      padding: 6px 14px;
      background-color: #2ecc71;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    .filter-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    .filter-bar a {
      margin: 0 10px;
      color: #3498db;
      text-decoration: none;
    }

    .filter-bar a.active {
      font-weight: bold;
      color: #2c3e50;
    }

    .error {
      color: red;
      font-weight: bold;
      margin-bottom: 15px;
      text-align: center;
    }
  </style>
</head>
<body>

<div class="header_container">
  <div class="header">
    <div class="name">PetSphere</div>
    <nav id="nav_bar">
      <a href="home.php">Home</a>
      <a href="pet_supplies.php">Pet Supplies</a>
      <a href="appointments.php" class="active">Appointments</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="veterinarian_services.php">Veterinarian Services</a>
      <a href="pets.php">My Pets</a>
    </nav>
    <div class="header-right">
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
  <h2>My Appointments</h2>

  <div class="filter-bar">
    <a href="appointments.php" class="<?= $status_filter === '' ? 'active' : '' ?>">All</a>
    <a href="appointments.php?status=confirmed" class="<?= $status_filter === 'confirmed' ? 'active' : '' ?>">Confirmed</a>
    <a href="appointments.php?status=completed" class="<?= $status_filter === 'completed' ? 'active' : '' ?>">Completed</a>
    <a href="appointments.php?status=cancelled" class="<?= $status_filter === 'cancelled' ? 'active' : '' ?>">Cancelled</a>
  </div>

  <?php if ($error): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="appointment-card">
      <strong>Vet:</strong> <?= htmlspecialchars($row['vet_name']) ?><br>
      <strong>Service:</strong> <?= htmlspecialchars($row['service_name']) ?> (<?= $row['duration_minutes'] ?> mins)<br>
      <strong>Price:</strong> Rs. <?= $row['price'] ?><br>
      <strong>Location:</strong> <?= htmlspecialchars($row['location']) ?><br>
      <strong>Date:</strong> <?= $row['appointment_date'] ?><br>
      <strong>Time:</strong> <?= date("g:i A", strtotime($row['appointment_time'])) ?><br>
      <strong>Status:</strong> <span class="status <?= $row['status'] ?>"><?= $row['status'] ?></span><br>
      <?php if (!empty($row['notes'])): ?>
        <strong>Notes:</strong> <?= htmlspecialchars($row['notes']) ?><br>
      <?php endif; ?>

      <?php if ($row['status'] === 'confirmed'): ?>
        <a class="cancel-btn" href="appointments.php?cancel=<?= $row['appointment_id'] ?>">Cancel</a>
        <form method="POST" class="reschedule-form">
          <input type="hidden" name="reschedule_id" value="<?= $row['appointment_id'] ?>">
          <input type="date" name="new_date" required>
          <input type="time" name="new_time" required>
          <button type="submit">Reschedule</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endwhile; ?>
</div>

<script>
  document.getElementById('menu-toggle').addEventListener('click', function () {
    const nav = document.getElementById('nav_bar');
    nav.classList.toggle('show');
  });
</script>

</body>
</html>
