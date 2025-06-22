<?php
session_start();
include("../User/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../User/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$_SESSION['reset_email']=$user['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
        }

        /* Header Styling */
        header {
            background-color: #333;
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 22px;
        }

        header a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: 500;
        }

        header a:hover {
            text-decoration: none;
            background-color:rgb(91, 101, 101);
            padding: 5px 10px;
            border-radius: 5px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #333;
            margin-bottom: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        p {
            margin: 8px 0;
            font-size: 16px;
        }

        .btn {
            display: inline-block;
            margin: 10px 5px;
            padding: 10px 15px;
            background:#333;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn:hover {
            background:rgb(91, 101, 101);
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="view_products.php">Products</a>
        <a href="profile.php">Profile</a>
        <a href="../User/index.html">Logout</a>
    </nav>
</header>

<!-- Profile Section -->
<div class="container">
    <h2>Admin Profile</h2>

    <?php
    $profileImage = !empty($user['profile_image']) ? $user['profile_image'] : 'default.png';
    echo "<img src='uploads/$profileImage' class='profile-img' alt='Profile Picture'>";
    ?>

    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>

    <div>
        <a class="btn" href="edit_profile.php">Edit Profile</a>
        <a class="btn" href="change_password.php">Change Password</a>
    </div>
</div>

</body>
</html>


