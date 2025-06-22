<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['pet_id'])) {
    echo "No pet ID provided.";
    exit;
}

$pet_id = $_GET['pet_id'];
$user_id = $_SESSION['user_id'];

// Check if the pet belongs to the logged-in user
$pet_check = mysqli_query($conn, "SELECT * FROM pets WHERE pet_id = '$pet_id' AND added_by = '$user_id'");
if (mysqli_num_rows($pet_check) === 0) {
    echo "Pet not found or access denied.";
    exit;
}

// Fetch pet history
$history_query = "SELECT * FROM pet_history WHERE pet_id = '$pet_id' ORDER BY date DESC";
$history_result = mysqli_query($conn, $history_query);

// Cart count
$cart_count = 0;
$cart_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$user_id'");
if ($cart_row = mysqli_fetch_assoc($cart_result)) {
    $cart_count = $cart_row['total_items'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pet History</title>
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

        .auth-btn {
            position: relative;
            font-size: 20px;
            background: none;
            border: none;
            color: white;
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

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #0077cc;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a.back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            background-color: rgb(54, 52, 52);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }

        a.back-btn:hover {
            background-color: #005fa3;
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
            <button class="auth-btn" onclick="window.location.href='cart.php'">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </button>
            <button class="auth-btn" onclick="window.location.href='profile.php'">
                <i class="fa-regular fa-user"></i>
            </button>
            <div class="menu-toggle" id="menu-toggle">&#9776;</div>
        </div>
    </div>
</div>

<div class="container">
    <a href="pets.php" class="back-btn">&larr; Back to My Pets</a>
    <h2>Pet Medical History</h2>
    <?php if (mysqli_num_rows($history_result) > 0): ?>
        <table>
            <tr>
                <th>Date</th>
                <th>Treatment</th>
                <th>Notes</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                    <td><?php echo htmlspecialchars($row['notes']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No medical history found for this pet.</p>
    <?php endif; ?>
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
