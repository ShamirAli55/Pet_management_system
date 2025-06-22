<?php
include("connection.php");
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$user_id'");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'] === 'other' ? $_POST['custom_type'] : $_POST['type'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $description = $_POST['description'];
    $image_path = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = "../User/uploads/";
        $image_path = $upload_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    $query = "INSERT INTO pets (name, type, breed, age, gender, description, image_url, added_by)
              VALUES ('$name', '$type', '$breed', '$age', '$gender', '$description', '$image_path', '$user_id')";
    mysqli_query($conn, $query);

    header("Location: pets.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Pet</title>
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

        .auth-btn {
            position: relative;
            font-size: 20px;
            background: none;
            border: none;
            cursor: pointer;
            color: white;
        }

        .auth-btn:hover {
            color: var(--primary);
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

        .menu-toggle {
            display: none;
            font-size: 24px;
            color: white;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            margin-left: 10px;
            align-self: center;
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

        .container {
            max-width: 650px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: rgb(54, 52, 52);
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.95rem;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: var(--hover);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button[type="submit"] {
            margin-top: 20px;
            width: 100%;
            background-color:rgb(44, 58, 47);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color:rgb(68, 78, 70);
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>

<!-- HEADER -->
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
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="auth-btn" onclick="window.location.href='cart.php'">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </button>
            <button class="auth-btn" onclick="window.location.href='profile.php'">
                <i class="fa-regular fa-user"></i>
            </button>
            <button class="menu-toggle" id="menu-toggle">&#9776;</button>
        </div>
    </div>
</div>

<!-- FORM CONTAINER -->
<div class="container">
    <a href="pets.php" class="back-btn">&larr; Back</a>

    <h2>Add New Pet</h2>
    <form method="post" enctype="multipart/form-data">
        <label>Name</label>
        <input type="text" name="name" required>

        <label>Type</label>
        <select name="type" id="type-select" required onchange="toggleCustomType(this)">
            <option value="dog">Dog</option>
            <option value="cat">Cat</option>
            <option value="bird">Bird</option>
            <option value="other">Other</option>
        </select>

        <div id="custom-type-container" class="hidden">
            <label>Custom Type</label>
            <input type="text" name="custom_type" placeholder="Enter your pet type">
        </div>

        <label>Breed</label>
        <input type="text" name="breed">

        <label>Age</label>
        <input type="number" name="age" min="0">

        <label>Gender</label>
        <select name="gender">
            <option value="male">Male</option>
            <option value="female">Female</option>
        </select>

        <label>Description</label>
        <textarea name="description"></textarea>

        <label>Image</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Add Pet</button>
    </form>
</div>

<script>
    const toggle = document.getElementById('menu-toggle');
    const nav = document.getElementById('nav_bar');
    toggle.addEventListener('click', () => {
        nav.classList.toggle('show');
    });

    function toggleCustomType(select) {
        const container = document.getElementById('custom-type-container');
        container.classList.toggle('hidden', select.value !== 'other');
    }
</script>

</body>
</html>
