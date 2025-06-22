<?php
include("connection.php");
session_start(); 
$user = $_SESSION['user_id'];
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$user'");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>PetSphere</title>
  <script src="https://kit.fontawesome.com/647670bc4e.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="./Style/home.css" />
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

  <div class="carousel-container">
    <div class="carousel" id="carousel">
      <div class="carousel-slide">
        <img src="./Images/image1.jpg" alt="Image 1">
      </div>
      <div class="carousel-slide">
        <img src="./Images/image2.jpg" alt="Image 2">
      </div>
      <div class="carousel-slide">
        <img src="./Images/image3.jpg" alt="Image 3">
      </div>
    </div>
  </div>

  <div class="new">
    <h4 style="padding:20px;font-size:20px">All our new arrivals and featured products</h4>
  </div>

  <div class="product-grid">
    <?php
    $query = "SELECT * FROM products WHERE stock > 0 ORDER BY product_id DESC LIMIT 6";
    $result = mysqli_query($conn, $query);

    if (!$result) {
      echo "Error: " . mysqli_error($conn);
      exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
      ?>
      <div class="product-card">
        <a href="description.php?product_id=<?= $row['product_id'] ?>">
          <img src="./uploads/<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['name']) ?> Image">
          <div class="product-details">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><del>Rs. <?= htmlspecialchars($row['old_price']) ?></del> Rs. <?= htmlspecialchars($row['new_price']) ?></p>
            <p>For: <?= htmlspecialchars($row['for_whom']) ?></p>
            <p>Category: <?= htmlspecialchars($row['category']) ?></p>
            <p>Stock: <?= htmlspecialchars($row['stock']) ?></p>
          </div>
        </a>
        <div class="product-actions">
          <form method="POST" action="cart.php">
            <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit">Add to Cart</button>
          </form>
        </div>
      </div>
      <?php
    }
    ?>
  </div>

  <div style="text-align:center; margin: 20px;">
    <a href="pet_supplies.php">
      <button style="padding:8px 16px; background-color:#444; color:#fff; border:none; border-radius:6px; cursor:pointer;">
        View All Products
      </button>
    </a>
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
