<?php
include("../User/connection.php");

// Redirect if no ID
if (!isset($_GET['edit'])) {
    header("Location: view_products.php");
    exit;
}

$id = intval($_GET['edit']);
$query = "SELECT * FROM products WHERE product_id = $id";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "❌ Product not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $old_price   = floatval($_POST['old_price']);
    $new_price   = floatval($_POST['new_price']);
    $stock       = intval($_POST['stock']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $for_whom    = mysqli_real_escape_string($conn, $_POST['for_whom']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $update = "UPDATE products SET 
        name='$name',
        old_price=$old_price,
        new_price=$new_price,
        stock=$stock,
        category='$category',
        for_whom='$for_whom',
        description='$description'
        WHERE product_id=$id";
    
    if (mysqli_query($conn, $update)) {
        header("Location: view_products.php");
        exit;
    } else {
        echo "❌ Error updating product: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            font-weight: 500;
        }
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-actions input[type="submit"],
        .form-actions a {
            padding: 10px 16px;
            text-decoration: none;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }
        .form-actions input[type="submit"] {
            background-color: var(--primary);
            color: white;
        }
        .form-actions input[type="submit"]:hover {
            background-color: var(--hover);
        }
        textarea {
    width: 100%;
    padding: 10px;
    margin: 8px 0 20px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
    resize: vertical; /* allows vertical resizing */
    min-height: 100px;
}

        .form-actions a {
            background-color: #ccc;
            color: black;
        }
        .form-actions a:hover {
            background-color: #aaa;
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

<div class="form-container">
    <h2>Edit Product</h2>
    <form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

    <label>Old Price:</label>
    <input type="number" step="0.01" name="old_price" value="<?= $product['old_price'] ?>" required>

    <label>New Price:</label>
    <input type="number" step="0.01" name="new_price" value="<?= $product['new_price'] ?>" required>

    <label>Stock:</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

    <label>Category:</label>
    <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

    <label>For Whom:</label>
    <input type="text" name="for_whom" value="<?= htmlspecialchars($product['for_whom']) ?>" required>

    <label>Description:</label>
    <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea>

    <div class="form-actions">
        <input type="submit" value="Update Product">
        <a href="view_products.php">Cancel</a>
    </div>
</form>
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
