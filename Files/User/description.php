<?php
include("connection.php");
session_start();

if (!isset($_GET['product_id'])) {
    echo "No product selected.";
    exit;
}

$product_id = (int) $_GET['product_id'];

$query = "SELECT * FROM products WHERE product_id = $product_id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "Product not found.";
    exit;
}

$product = mysqli_fetch_assoc($result);

// Fetch similar products based on 'for_whom'
$for_whom = mysqli_real_escape_string($conn, $product['for_whom']);
$similar_query = "SELECT * FROM products 
                  WHERE for_whom = '$for_whom' AND product_id != $product_id 
                  AND stock > 0 
                  ORDER BY RAND() 
                  LIMIT 4";
$similar_result = mysqli_query($conn, $similar_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f3f3;
      margin: 0;
      padding: 0;
    }

    .product-container {
      display: flex;
      flex-wrap: wrap;
      max-width: 1200px;
      margin: 40px auto 20px;
      background-color: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 12px;
      overflow: hidden;
    }

    .image-section {
      flex: 1;
      min-width: 300px;
      background-color: #fafafa;
      padding: 30px;
      text-align: center;
    }

    .image-section img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
    }

    .info-section {
      flex: 1;
      min-width: 320px;
      padding: 30px;
    }

    .info-section h1 {
      font-size: 2rem;
      margin-bottom: 10px;
      color: #333;
    }

    .info-section .price {
      font-size: 1.8rem;
      font-weight: bold;
      color: #e53935;
      margin: 10px 0;
    }

    .info-section .price del {
      color: #777;
      font-size: 1rem;
      margin-left: 10px;
    }

    .info-section p {
      margin: 10px 0;
      font-size: 1rem;
      color: #555;
    }

    .highlight {
      font-weight: 600;
      color: #222;
    }

    .actions {
      margin-top: 25px;
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: center;
    }

    .btn {
      padding: 12px 24px;
      font-size: 1rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn.cart {
      background-color: #0077cc;
      color: white;
    }

    .btn.cart:hover {
      background-color: #005fa3;
    }

    .btn.back {
      background-color: #555;
      color: white;
    }

    .btn.back:hover {
      background-color: #333;
    }

    .quantity-input {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .quantity-input input {
      width: 60px;
      padding: 6px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      text-align: center;
    }

    /* Description section */
    .description-section {
      max-width: 1200px;
      margin: 30px auto;
      padding: 20px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    .description-section h2 {
      font-size: 1.5rem;
      margin-bottom: 10px;
      color: #333;
    }

    .description-section p {
      line-height: 1.7;
      color: #555;
      font-size: 1rem;
    }

    /* Similar Products */
    .similar-section {
      max-width: 1200px;
      margin: 30px auto 60px;
      padding: 0 20px;
    }

    .similar-section h2 {
      font-size: 1.6rem;
      margin-bottom: 20px;
      color: #333;
    }

    .similar-products {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
    }

    .product-card {
      width: 220px;
      border: 1px solid #ddd;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      background-color: #fff;
      text-align: center;
      transition: transform 0.2s ease;
    }

    .product-card:hover {
      transform: translateY(-5px);
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
      margin: 10px;
    }

    .product-actions button {
      padding: 8px 14px;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      background-color: #0077cc;
      transition: background-color 0.3s;
    }

    .product-actions button:hover {
      background-color: #005fa3;
    }

    .product-card a {
      text-decoration: none;
      color: inherit;
    }

    @media (max-width: 768px) {
      .product-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<!-- Product Details -->
<div class="product-container">
  <div class="image-section">
    <img src="./uploads/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
  </div>
  <div class="info-section">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="price">
      Rs. <?= number_format($product['new_price'], 2) ?>
      <del>Rs. <?= number_format($product['old_price'], 2) ?></del>
    </div>

    <p><span class="highlight">Category:</span> <?= htmlspecialchars($product['category']) ?></p>
    <p><span class="highlight">Type:</span> <?= htmlspecialchars($product['product_type']) ?></p>
    <p><span class="highlight">For:</span> <?= htmlspecialchars($product['for_whom']) ?></p>
    <p><span class="highlight">Stock:</span> <?= (int) $product['stock'] ?> available</p>
    <div class="actions">
  <form method="POST" action="cart.php" onsubmit="return validateQuantity(<?= $product['stock'] ?>)" style="display: flex; align-items: center; gap: 15px;">
    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <div class="quantity-input">
      <label for="qty">Qty:</label>
      <input type="number" id="qty" name="quantity" min="1" max="<?= $product['stock'] ?>" value="1" required>
    </div>
    <button type="submit" class="btn cart">🛒 Add to Cart</button>
  </form>
  <a href="pet_supplies.php" class="btn back">← Back to Products</a>
</div>

  </div>
</div>

<!-- Product Description Section -->
<div class="description-section">
  <h2>Product Description</h2>
  <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
</div>

<!-- You May Also Like Section -->
<?php if (mysqli_num_rows($similar_result) > 0): ?>
  <div class="similar-section">
    <h2>You May Also Like</h2>
    <div class="similar-products">
      <?php while ($item = mysqli_fetch_assoc($similar_result)): ?>
        <div class="product-card">
          <a href="description.php?product_id=<?= $item['product_id'] ?>">
            <img src="./uploads/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            <div class="product-details">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <p><del>Rs. <?= number_format($item['old_price']) ?></del> Rs. <?= number_format($item['new_price']) ?></p>
              <p>Stock: <?= (int)$item['stock'] ?></p>
            </div>
          </a>
          <div class="product-actions">
            <form method="POST" action="cart.php">
              <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
              <input type="hidden" name="quantity" value="1">
              <button type="submit">Add to Cart</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endif; ?>

<script>
function validateQuantity(maxStock) {
  const qty = parseInt(document.getElementById('qty').value);
  if (qty > maxStock) {
    alert("Quantity cannot exceed available stock.");
    return false;
  }
  return true;
}
</script>

</body>
</html>
