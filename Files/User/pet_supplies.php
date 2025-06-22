<?php
include("connection.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Filters
$category = $_GET['category'] ?? '';
$subcategory = $_GET['subcategory'] ?? '';
$type = $_GET['type'] ?? '';
$for_whom = $_GET['for_whom'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? '';

// Default condition
$conditions = ["stock > 0"];
$is_filtered = false;

if (!empty($category)) {
    $safe_category = mysqli_real_escape_string($conn, strtolower($category));
    $conditions[] = "LOWER(category) = '$safe_category'";
    $is_filtered = true;
}
if (!empty($subcategory)) {
    $safe_subcategory = mysqli_real_escape_string($conn, strtolower($subcategory));
    $conditions[] = "LOWER(subcategory) = '$safe_subcategory'";
    $is_filtered = true;
}
if (!empty($type)) {
    $safe_type = mysqli_real_escape_string($conn, $type);
    $conditions[] = "product_type = '$safe_type'";
    $is_filtered = true;
}
if (!empty($for_whom)) {
    $safe_fw = mysqli_real_escape_string($conn, $for_whom);
    $conditions[] = "for_whom = '$safe_fw'";
    $is_filtered = true;
}
if (is_numeric($min_price) && $min_price > 0) {
    $conditions[] = "new_price >= $min_price";
    $is_filtered = true;
}
if (is_numeric($max_price) && $max_price > 0) {
    $conditions[] = "new_price <= $max_price";
    $is_filtered = true;
}

$order_by = "ORDER BY name ASC";
if ($sort === 'price_asc') {
    $order_by = "ORDER BY new_price ASC";
    $is_filtered = true;
} elseif ($sort === 'price_desc') {
    $order_by = "ORDER BY new_price DESC";
    $is_filtered = true;
}

$filter_condition = implode(" AND ", $conditions);
$query = "SELECT * FROM products WHERE $filter_condition $order_by LIMIT 12";
$result = mysqli_query($conn, $query);

// Cart count
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $user_id");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pet Product Store</title>
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
      margin-bottom: 1rem;
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
    .header-right {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .auth-btn {
      background-color: #333;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s;
      font-size: 20px;
    }
    .auth-btn:hover {
      background-color: #555;
    }
    .menu-toggle {
      display: none;
      font-size: 22px;
      color: white;
      cursor: pointer;
      background-color: #333;
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

    .top-search-bar {
      display: flex;
      justify-content: center;
      padding: 10px 0;
      background: #f9f9f9;
      margin-bottom: 20px;
    }
    .top-search-bar button:hover {
      background-color: #555;
    }
    .top-search-bar form {
      display: flex;
      gap: 8px;
    }
    .top-search-bar input[type="text"] {
      padding: 10px 14px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 15px;
      min-width: 260px;
    }
    .top-search-bar button {
      background-color: #333;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
    }

    .filter-bar {
      text-align: center;
      padding: 20px;
      background-color: #f9f9f9;
    }
    .filter-bar form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
      align-items: center;
    }
    .filter-input,
    .filter-select {
      padding: 10px 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
      min-width: 160px;
      background-color: #fff;
      transition: border-color 0.3s;
    }
    .filter-input:focus,
    .filter-select:focus {
      border-color: var(--primary);
      outline: none;
    }

    .book-grid {
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
  transition: transform 0.3s, box-shadow 0.3s;
  cursor: pointer;
}
.product-card:hover {
  transform: scale(1.03);
  box-shadow: 0 6px 12px rgba(0,0,0,0.15);
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
    .product-actions form {
      margin-top: 10px;
    }
    .product-actions button {
      padding: 8px 16px;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      background-color: var(--primary);
    }
    .product-actions button:hover {
      background-color: var(--hover);
    }

    .cart-button-wrapper {
      position: relative;
      display: inline-block;
    }
    .cart-button-wrapper .auth-btn {
      position: relative;
    }
    .cart-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background-color: red;
      color: white;
      font-size: 11px;
      padding: 2px 5px;
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
      <a href="pet_supplies.php" class="active">Pet Supplies</a>
      <a href="appointments.php">Appointments</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="veterinarian_services.php">Veterinarian Services</a>
      <a href="pets.php">My Pets</a>
    </nav>
    <div class="header-right">
      <div class="cart-button-wrapper">
        <button class="auth-btn" onclick="window.location.href='cart.php'"><i class="fa-solid fa-cart-shopping"></i><?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?></button>
        <button class="auth-btn" onclick="window.location.href='profile.php'"><i class="fa-regular fa-user"></i></button>
      </div>
      <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    </div>
  </div>
</div>

<!-- Search Bar -->
<div class="top-search-bar">
  <form action="filter_search.php" method="GET">
    <input type="text" name="query" placeholder="Search for pet products..." required>
    <button type="submit"><i class="fa fa-search"></i> Search</button>
  </form>
</div>

<!-- Filter Section -->
<div class="filter-bar">
  <form method="GET" action="">
    <select class="filter-select" name="for_whom">
      <option value="">For Whom</option>
      <option value="dog" <?= $for_whom === 'dog' ? 'selected' : '' ?>>Dog</option>
      <option value="cat" <?= $for_whom === 'cat' ? 'selected' : '' ?>>Cat</option>
      <option value="bird" <?= $for_whom === 'bird' ? 'selected' : '' ?>>Bird</option>
      <option value="all" <?= $for_whom === 'all' ? 'selected' : '' ?>>All</option>
    </select>
    <input class="filter-input" type="number" name="min_price" placeholder="Min Price" min="0" value="<?= htmlspecialchars($min_price) ?>">
    <input class="filter-input" type="number" name="max_price" placeholder="Max Price" min="0" value="<?= htmlspecialchars($max_price) ?>">
    <select class="filter-select" name="sort">
      <option value="">Sort</option>
      <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
    </select>
    <button type="submit" class="auth-btn">Apply</button>
  </form>
</div>

<!-- Product Grid -->
<div class="book-grid">
  <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <div class="product-card" onclick="window.location.href='description.php?product_id=<?= $row['product_id'] ?>'">
      <img src="./uploads/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
      <div class="product-details">
        <h3><?= htmlspecialchars($row['name']) ?></h3>
        <p><del>Rs. <?= htmlspecialchars($row['old_price']) ?></del> Rs. <?= htmlspecialchars($row['new_price']) ?></p>
        <p>For: <?= htmlspecialchars($row['for_whom']) ?></p>
        <p>Category: <?= htmlspecialchars($row['category']) ?></p>
        <p>Stock: <?= htmlspecialchars($row['stock']) ?></p>
      </div>
      <div class="product-actions">
        <form method="POST" action="cart.php">
          <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
          <input type="hidden" name="quantity" value="1">
          <button type="submit">Add to Cart</button>
        </form>
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
