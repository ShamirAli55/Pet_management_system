<?php
include("connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle delete request
if (isset($_GET['delete'])) {
    $pet_id = $_GET['delete'];
    $query = "DELETE FROM pets WHERE pet_id = $pet_id AND added_by = $user_id";
    mysqli_query($conn, $query);
    header("Location: pets.php");
    exit;
}

// Fetch pets
$query = "SELECT * FROM pets WHERE added_by = $user_id";
$result = mysqli_query($conn, $query);

$user = $_SESSION['user_id'];
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$user'");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Pets</title>
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

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        .pet {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .pet img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
        }

        .pet-info {
            flex-grow: 1;
        }

        .actions a {
            margin: 5px 0;
            text-decoration: none;
            color: white;
            background: #007bff;
            padding: 6px 10px;
            border-radius: 20%;
            display: inline-block;
        }

        .actions a.delete {
            background: #dc3545;
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

<div class="container">
    <h2>My Pets</h2><br>
    <a href="add_pet.php" style="margin-bottom: 15px; display: inline-block; background: green; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none;">Add New Pet</a><br><hr><br>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="pet">
            <img src="<?php echo $row['image_url'] ? htmlspecialchars($row['image_url']) : 'default.png'; ?>" alt="Pet Image">
            <div class="pet-info">
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p>Type: <?php echo htmlspecialchars($row['type']); ?> | Breed: <?php echo htmlspecialchars($row['breed']); ?></p>
                <p>Age: <?php echo htmlspecialchars($row['age']); ?> | Gender: <?php echo htmlspecialchars($row['gender']); ?></p>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
            <div class="actions">
                <a href="pet_history.php?pet_id=<?php echo $row['pet_id']; ?>" style="background-color:#17a2b8;border-radius:10px;">View History</a><br>
                <a href="edit_pet.php?id=<?php echo $row['pet_id']; ?>" style="background-color:blue;">Edit</a>
                <a href="delete_pet.php?id=<?php echo $row['pet_id']; ?>" onclick="return confirm('Are you sure you want to delete this pet?')" style="background-color:red;">Delete</a>
            </div>
        </div>
    <?php endwhile; ?>
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
