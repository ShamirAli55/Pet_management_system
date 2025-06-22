<?php
include("../User/connection.php");
session_start();

// Reject appointment handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_id'])) {
    $reject_id = intval($_POST['reject_id']);
    $update_query = "UPDATE appointments SET status = 'cancelled' WHERE appointment_id = $reject_id";
    mysqli_query($conn, $update_query);
    header("Location: appointments.php");
    exit();
}

// Fetch all appointments with joins
$query = "
    SELECT 
        a.*, 
        u.username AS user_name, 
        v.name AS vet_name,
        s.service_name AS service_name
    FROM appointments a
    JOIN users u ON a.user_id = u.user_id
    JOIN veterinarians v ON a.vet_id = v.vet_id
    JOIN vet_services s ON a.service_id = s.service_id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
";

$appointments = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Appointment Management</title>
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

    h1 {
      text-align: center;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 12px 16px;
      border-bottom: 1px solid #eee;
      text-align: left;
    }
    th {
      background-color: #333;
      color: white;
    }
    tr:hover {
      background-color: #f9f9f9;
    }
    .reject-btn {
      padding: 6px 12px;
      background: #e74c3c;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .reject-btn:hover {
      background: #c0392b;
    }
    form {
      margin: 0;
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


<h1>Appointment Management</h1>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>User</th>
      <th>Veterinarian</th>
      <th>Service</th>
      <th>Date</th>
      <th>Time</th>
      <th>Status</th>
      <th>Notes</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($appointments)): ?>
    <tr>
      <td><?php echo $row['appointment_id']; ?></td>
      <td><?php echo htmlspecialchars($row['user_name']); ?></td>
      <td><?php echo htmlspecialchars($row['vet_name']); ?></td>
      <td><?php echo htmlspecialchars($row['service_name']); ?></td>
      <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
      <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
      <td><?php echo ucfirst($row['status']); ?></td>
      <td><?php echo htmlspecialchars($row['notes']); ?></td>
      <td>
        <?php if ($row['status'] !== 'cancelled'): ?>
          <form method="POST" onsubmit="return confirm('Reject this appointment?');">
            <input type="hidden" name="reject_id" value="<?php echo $row['appointment_id']; ?>">
            <button type="submit" class="reject-btn">Reject</button>
          </form>
        <?php else: ?>
          <span style="color: red;">Cancelled</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<script>
    const toggle = document.getElementById('menu-toggle');
    const nav = document.getElementById('nav_bar');
    toggle.addEventListener('click', () => {
      nav.classList.toggle('show');
    });
  </script>
</body>
</html>
