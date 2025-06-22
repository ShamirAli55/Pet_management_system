<?php
include("../User/connection.php");
session_start();
$edit_mode = false;

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE product_id = $id");
    header("Location: view_products.php");
    exit();
}

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = intval($_GET['edit']);
    $edit_result = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $id");
    $product = mysqli_fetch_assoc($edit_result);
}

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $new_price = floatval($_POST['price']);
    $old_price = floatval($_POST['old_price']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $stock = intval($_POST['stock']);
    $image_name = $_FILES['image']['name'];
    $tempname = $_FILES['image']['tmp_name'];
    $folder = 'uploads/' . $image_name;

    if ($edit_mode) {
        $update_query = "UPDATE products SET name='$name', new_price=$new_price, old_price=$old_price, brand='$brand', category='$category', stock=$stock";
        if (!empty($image_name)) {
            $update_query .= ", image_url='$image_name'";
        }
        $update_query .= " WHERE product_id=$id";

        if (mysqli_query($conn, $update_query)) {
            if (!empty($image_name)) move_uploaded_file($tempname, $folder);
            header("Location: view_products.php");
            exit();
        } else {
            echo "<div class='message'>Error: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $query = "INSERT INTO products (name, new_price, old_price, brand, category, stock, image_url) 
                  VALUES ('$name', $new_price, $old_price, '$brand', '$category', $stock, '$image_name')";
        if (mysqli_query($conn, $query) && move_uploaded_file($tempname, $folder)) {
            header("Location: view_products.php");
            exit();
        } else {
            echo "<div class='message'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $edit_mode ? 'Edit Product' : 'Add Product' ?></title>
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

    .containerr {
      max-width: 800px;
      margin: 30px auto;
      background: var(--card-bg);
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 8px var(--card-shadow);
      position: relative;
    }
    .containerr h2 {
      color: var(--header);
      margin-top: 30px;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: 500;
    }
    input, select {
      width: 100%;
      padding: 8px;
      margin-top: 6px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    input[type="submit"] {
      margin-top: 20px;
      background-color: var(--primary);
      color: white;
      border: none;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background-color: var(--hover);
    }
    .back-link {
      /* position: absolute; */
      top: 10px;
      left: 30px;
      text-decoration: none;
      color: var(--card-bg);
      margin-right: 0px;
      background-color: var(--header);
      padding: 10px;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      font-weight: 600;
      font-size: 16px;
    }
    .back-link i {
      margin-right: 6px;
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
<a href="view_products.php" class="back-link"><i class="fa-solid fa-arrow-left-long"></i></a>
  <h2><?= $edit_mode ? 'Edit Product' : 'Add New Product' ?></h2>
  <form method="post" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="name" value="<?= $edit_mode ? htmlspecialchars($product['name']) : '' ?>" required>

    <label>New Price</label>
    <input type="number" step="0.01" name="price" value="<?= $edit_mode ? $product['new_price'] : '' ?>" required>

    <label>Old Price</label>
    <input type="number" step="0.01" name="old_price" value="<?= $edit_mode ? $product['old_price'] : '' ?>">

    <label>Brand</label>
    <input type="text" name="brand" value="<?= $edit_mode ? htmlspecialchars($product['brand']) : '' ?>" required>

    <label>Category</label>
    <select name="category" required>
      <option value="food" <?= $edit_mode && $product['category'] === 'food' ? 'selected' : '' ?>>Food</option>
      <option value="toy" <?= $edit_mode && $product['category'] === 'toy' ? 'selected' : '' ?>>Toy</option>
      <option value="accessory" <?= $edit_mode && $product['category'] === 'accessory' ? 'selected' : '' ?>>Accessory</option>
      <option value="medicine" <?= $edit_mode && $product['category'] === 'medicine' ? 'selected' : '' ?>>Medicine</option>
    </select>

    <label>Stock</label>
    <input type="number" name="stock" value="<?= $edit_mode ? $product['stock'] : '' ?>" required>

    <label>Product Image</label>
    <input type="file" name="image" <?= $edit_mode ? '' : 'required' ?>>

    <input type="submit" name="submit" value="<?= $edit_mode ? 'Update Product' : 'Add Product' ?>">
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
