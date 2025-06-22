<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info including profile image
$query = "SELECT username, email, role, created_at, profile_image FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$user_id'");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}

$avatar_file = !empty($user['profile_image']) ? $user['profile_image'] : 'Avatars/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
    color: white !important;
    cursor: pointer;
    background-color: #333;
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
    display: flex !important; 
    background-color: var(--header);
  }
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
    }

    .cart-button-wrapper {
      position: relative;
    }

    .profile-container {
      max-width: 600px;
      margin: 30px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
    }

    .profile-pic {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #333;
      margin-bottom: 20px;
    }

    h2 {
      margin-bottom: 20px;
      color: #333;
    }

    .profile-field {
      margin: 12px 0;
    }

    .profile-field label {
      font-weight: 600;
      color: #555;
    }

    .profile-field span {
      display: block;
      color: #111;
      margin-top: 3px;
    }

    .edit-btn, .delete-btn {
      display: inline-block;
      margin: 12px 8px 0 8px;
      padding: 10px 18px;
      color: white;
      border-radius: 6px;
      font-weight: bold;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .edit-btn {
      background-color: #28a745;
    }

    .edit-btn:hover {
      background-color: #218838;
    }

    .delete-btn {
      background-color: rgb(231, 9, 24);
    }

    .delete-btn:hover {
      background-color: hsla(8, 85%, 49%, 0.9);
    }


    @media (max-width: 768px) {
      #nav_bar {
        display: none;
        flex-direction: column;
        background-color: var(--header);
        padding: 10px;
        position: absolute;
        top: 60px;
        left: 0;
        width: 100%;
      }

      #nav_bar.show {
        display: flex !important;
      }

      .menu-toggle {
        display: block;
        font-size: 22px;
        color: white;
        background-color: #333;
        cursor: pointer;
      }
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
        <a href="appointments.php">Appointments</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="veterinarian_services.php">Veterinarian Services</a>
        <a href="pets.php">My Pets</a>
      </nav>

      <div class="header-right">
        <div class="cart-button-wrapper">
          <button class="auth-btn" onclick="window.location.href='cart.php'">
            <i class="fa-solid fa-cart-shopping"></i>
            <?php if ($cart_count > 0): ?>
              <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
          </button>
          <button class="auth-btn" onclick="window.location.href='profile.php'">
            <i class="fa-regular fa-user"></i>
          </button>
        </div>
        <div class="menu-toggle" id="menu-toggle">&#9776;</div>
      </div>
    </div>
  </div>
<div class="profile-container">
  <img src="<?= htmlspecialchars($avatar_file) . '?t=' . time() ?>" alt="Avatar" class="profile-pic">

  <h2><i class="fa-solid fa-user icon"></i> Your Profile</h2>

  <div class="profile-field">
    <label>Username:</label>
    <span><?= htmlspecialchars($user['username']) ?></span>
  </div>

  <div class="profile-field">
    <label>Email:</label>
    <span><?= htmlspecialchars($user['email']) ?></span>
  </div>

  <div class="profile-field">
    <label>Role:</label>
    <span><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
  </div>

  <div class="profile-field">
    <label>Account Created:</label>
    <span><?= date("F j, Y, g:i a", strtotime($user['created_at'])) ?></span>
  </div>

  <a class="edit-btn" href="edit_profile.php"><i class="fa-solid fa-pen-to-square icon"></i> Edit Profile</a>
  <a class="delete-btn" href="index.html"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
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
