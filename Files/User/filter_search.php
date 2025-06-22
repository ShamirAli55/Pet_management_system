<?php
include("connection.php");
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$search = isset($_GET['query']) ? trim($_GET['query']) : '';
if (empty($search)) {
    header("Location: pet_supplies.php");
    exit;
}

$safe_search = mysqli_real_escape_string($conn, strtolower($search));
$keywords = preg_split('/\s+/', $safe_search);

$conditions = ["stock > 0"];
$keyword_conditions = [];

foreach ($keywords as $word) {
    $word = mysqli_real_escape_string($conn, $word);
    $like = "LOWER(name) LIKE '%$word%' OR LOWER(description) LIKE '%$word%' OR LOWER(category) LIKE '%$word%' OR LOWER(product_type) LIKE '%$word%'";
    $keyword_conditions[] = "($like)";
}

if (!empty($keyword_conditions)) {
    $conditions[] = implode(" AND ", $keyword_conditions);
}

$query = "SELECT * FROM products WHERE " . implode(" AND ", $conditions) . " ORDER BY name ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

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
  <title>Search Results - PetSphere</title>
  <script src="https://kit.fontawesome.com/647670bc4e.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="./Style/pet_supplies.css"/>
</head>
<body>

<!-- Header -->
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
        <button class="auth-btn" onclick="window.location.href='cart.php'">
          <i class="fa-solid fa-cart-shopping"></i>
          <?php if ($cart_count > 0): ?>
            <span class="cart-badge"><?= $cart_count ?></span>
          <?php endif; ?>
        </button>
        <button class="auth-btn" onclick="window.location.href='profile.php'"><i class="fa-regular fa-user"></i></button>
      </div>
      <div class="menu-toggle" id="menu-toggle">&#9776;</div>
    </div>
  </div>
</div>

<!-- Back Button -->
<div style="padding: 20px 30px;">
  <a href="pet_supplies.php" style="
    display: inline-block;
    padding: 8px 16px;
    background-color:rgb(54, 52, 52);
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.95rem;
    transition: background 0.3s ease;
  " onmouseover="this.style.backgroundColor='#005fa3'" onmouseout="this.style.backgroundColor='rgb(54, 52, 52)'">
    &larr; 
  </a>
</div>

<!-- Search Results Heading -->
<div style="text-align:center; margin-top: 10px; font-size: 1.1rem; font-weight: 600;">
  Search results for:
  <span style="color: #0077cc;">
    "<?= htmlspecialchars($search) ?>"
  </span>
</div>

<!-- Product Grid -->
<div class="contain">
  <div class="book-grid">
    <?php if (mysqli_num_rows($result) > 0): ?>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="product-card">
          <img src="./uploads/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
          <div class="product-details">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><del>Rs. <?= htmlspecialchars($row['old_price']) ?></del> Rs. <?= htmlspecialchars($row['new_price']) ?></p>
            <p>For: <?= htmlspecialchars($row['for_whom']) ?></p>
            <p>Category: <?= htmlspecialchars($row['category']) ?></p>
            <p>Stock: <?= htmlspecialchars($row['stock']) ?></p>
          </div>
          <div class="product-actions">
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>" />
              <input type="hidden" name="quantity" value="1" />
              <button type="submit">Add to Cart</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="text-align:center; font-size: 1.1rem; margin-top: 40px; color: #888;">
        No products found for "<strong><?= htmlspecialchars($search) ?></strong>"
      </div>
    <?php endif; ?>
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
