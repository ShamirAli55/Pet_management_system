<?php
include("../User/connection.php");

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM products WHERE stock > 0 AND name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%' ORDER BY product_id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products</title>
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
        .search-bar {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .search-bar input[type="text"] {
            padding: 8px;
            width: 300px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .search-bar button {
            padding: 8px 12px;
            margin-left: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }
        .product-card {
            width: 200px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 10px;
            background-color: #fff;
            text-align: center;
        }
        .product-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .product-details h3 {
            font-size: 16px;
            margin: 10px 0 5px;
        }
        .product-details p {
            margin: 3px 0;
            font-size: 14px;
        }
        .action-buttons {
            margin-top: 10px;
        }
        .action-buttons a {
            margin: 0 3px;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-size: 13px;
        }
        .view-btn {
            background-color: var(--primary);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .view-btn:hover {
            background-color: var(--hover);
        }
        .edit-btn {
            background-color: #28a745;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .containerr {
            text-align: center;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1100px;
            margin: 0 auto;
            padding: 10px 20px;
        }
        .top-bar h2 {
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

<div class="containerr">
    <div class="top-bar">
        <h2>Available Products</h2>
        <a class="view-btn" href="manage_products.php">Add a Item</a>
    </div>
    <div class="product-grid">
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='product-card'>";
            echo "<img src='uploads/" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
            echo "<div class='product-details'>";
            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
            echo "<p><del>Rs. " . htmlspecialchars($row['old_price']) . "</del> Rs. " . htmlspecialchars($row['new_price']) . "</p>";
            echo "<p>For: " . htmlspecialchars($row['for_whom']) . "</p>";
            echo "<p>" . htmlspecialchars($row['category']) . "</p>";
            echo "<p>Stock: " . htmlspecialchars($row['stock']) . "</p>";
            echo "</div>";
            echo "<div class='action-buttons'>";
            echo "<a class='edit-btn' href='edit.php?edit=" . $row['product_id'] . "'>Edit</a>";
            echo "<a class='delete-btn' href='delete.php?delete=" . $row['product_id'] . "' onclick=\"return confirm('Are you sure you want to delete this product?')\">Delete</a>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p style='text-align:center'>No products found.</p>";
    }
    ?>
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
