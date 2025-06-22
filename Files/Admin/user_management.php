<?php
include("../User/connection.php");
session_start();

$admin_id = $_SESSION['user_id'] ?? 0;

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    $check_role = mysqli_query($conn, "SELECT role FROM users WHERE user_id = $delete_id");
    $row = mysqli_fetch_assoc($check_role);
    $role_to_delete = $row['role'] ?? '';

    if ($delete_id !== $admin_id && $role_to_delete !== 'admin') {
        mysqli_query($conn, "DELETE FROM users WHERE user_id = $delete_id");
    }

    header("Location: user_management.php");
    exit();
}

// Search logic
$search = $_GET['search'] ?? '';
$search_safe = mysqli_real_escape_string($conn, $search);

$sql = "SELECT * FROM users";
if (!empty($search)) {
    $sql .= " WHERE username LIKE '%$search_safe%' OR email LIKE '%$search_safe%' OR role LIKE '%$search_safe%'";
}
$sql .= " ORDER BY created_at DESC";
$user_query = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
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
      margin-bottom: 10px;
    }
    .search-bar {
      max-width: 400px;
      margin: 0 auto 20px;
      display: flex;
    }
    .search-bar input {
      flex: 1;
      padding: 8px 12px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 6px 0 0 6px;
      outline: none;
    }
    .search-bar button {
      padding: 8px 16px;
      border: none;
      background-color: rgb(33, 30, 30);
      color: white;
      font-weight: bold;
      border-radius: 0 6px 6px 0;
      cursor: pointer;
    }
    .search-bar button:hover {
      background-color:rgb(70, 70, 82);
    }
    .back-btn {
      text-align:left;
      margin-bottom: 20px;
      margin-left: 20px;
      margin-top:10px;
    }
    .back-btn a {
      display: inline-block;
      font-weight: bold;
      color:white;
      text-decoration: none;
      background-color: rgb(33, 30, 30);
      padding:10px;
      border-radius: 15px;
      font-size: 16px;
    }
    .back-btn a:hover {
      background-color:  rgb(70, 70, 82);
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
    .delete-btn {
      padding: 6px 12px;
      background: #e74c3c;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .delete-btn:hover {
      background: #c0392b;
    }
    form {
      margin: 0;
    }
    .disabled {
      color: gray;
      font-weight: bold;
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


<?php if (!empty($search)): ?>
  <div class="back-btn">
    <a href="user_management.php"><i class="fas fa-arrow-left"></i></a>
  </div>
<?php endif; ?>
<h1>User Management</h1>
<div class="search-bar">
  <form method="GET" action="">
    <input type="text" name="search" placeholder="Search by name, email or role..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
</div>
<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Email</th>
      <th>Role</th>
      <th>Created At</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php if (mysqli_num_rows($user_query) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($user_query)): ?>
      <tr>
        <td><?php echo $row['user_id']; ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo ucfirst($row['role']); ?></td>
        <td><?php echo $row['created_at']; ?></td>
        <td>
          <?php if ($row['role'] === 'admin'): ?>
            <span class="disabled">Admin</span>
          <?php elseif ($row['user_id'] === $admin_id): ?>
            <span class="disabled">You</span>
          <?php else: ?>
            <form method="POST" onsubmit="return confirm('Delete this user?');">
              <input type="hidden" name="delete_id" value="<?php echo $row['user_id']; ?>">
              <button type="submit" class="delete-btn">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6" style="text-align:center; padding: 20px;">No users found.</td></tr>
    <?php endif; ?>
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
