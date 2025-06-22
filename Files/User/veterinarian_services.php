<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$search_term = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : "";


$synonyms = [
    'vaccine' => 'vaccination',
    'checkup' => 'check-up',
    'deworm' => 'deworming',
    'injury' => 'emergency',
];
$original_search = $search_term;
if (array_key_exists($search_term, $synonyms)) {
    $search_term = $synonyms[$search_term];
}

$search_sql = $search_term ? "WHERE LOWER(s.service_name) LIKE '%$search_term%' OR LOWER(v.name) LIKE '%$search_term%'" : "";

$query = "SELECT s.*, v.name AS vet_name, v.email, v.phone, v.location, v.vet_id 
          FROM vet_services s 
          JOIN veterinarians v ON s.vet_id = v.vet_id 
          $search_sql
          ORDER BY s.service_name ASC";

$result = mysqli_query($conn, $query);
$rows = [];
while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

$userr = $_SESSION['user_id'];
$cart_count = 0;
$count_result = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = '$userr'");
if ($count_row = mysqli_fetch_assoc($count_result)) {
    $cart_count = $count_row['total_items'] ?? 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Veterinarian Services</title>
    <script src="https://kit.fontawesome.com/647670bc4e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../User/Style/veterinarian_services.css">
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
    <h2>Available Vet Services</h2>

    <?php if (!empty($original_search)): ?>
    <div style="padding: 20px 30px;">
        <a href="veterinarian_services.php" style="display: inline-block; padding: 8px 16px;
            background-color: rgb(54, 52, 52);color: #fff;
            text-decoration: none;border-radius: 5px;font-size: 0.95rem;transition: background 0.3s ease;"
            onmouseover="this.style.backgroundColor='#005fa3'" onmouseout="this.style.backgroundColor='rgb(54, 52, 52)'">
            &larr;
        </a>
    </div>
    <?php endif; ?>

    <div class="search-bar">
        <form method="get">
            <input type="text" name="search" placeholder="Search by service or vet name..." value="<?= htmlspecialchars($original_search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if ($original_search && empty($rows)): ?>
        <p class="info-message">No exact results found for "<strong><?= htmlspecialchars($original_search) ?></strong>"</p>
    <?php endif; ?>

    <?php foreach ($rows as $row): ?>
        <div class="card">
            <div style="flex:1;">
                <h3><?= htmlspecialchars($row['service_name']) ?></h3>
                <p><strong>Vet:</strong> <?= htmlspecialchars($row['vet_name']) ?></p>
                <p><strong>Price:</strong> Rs. <?= $row['price'] ?> | <strong>Duration:</strong> <?= $row['duration_minutes'] ?> minutes</p>
                <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                <form method="post" action="appointments.php">
                    <input type="hidden" name="vet_id" value="<?= $row['vet_id'] ?>">
                    <input type="hidden" name="service_id" value="<?= $row['service_id'] ?>">

                    <!-- 🐾 PET DROPDOWN -->
                    <label>Select Pet</label>
                    <select name="pet_id" required>
                        <option value="">-- Choose a Pet --</option>
                        <?php
                       $pet_result = mysqli_query($conn, "SELECT pet_id, name FROM pets WHERE added_by = $user_id");
                       if (!$pet_result) {
                           echo "<p style='color:red;'>Error fetching pets: " . mysqli_error($conn) . "</p>";
                       } elseif (mysqli_num_rows($pet_result) === 0) {
                           echo "<p style='color:orange;'>No pets found. Please add a pet first.</p>";
                       }
                       while ($pet = mysqli_fetch_assoc($pet_result)):
                       ?>
                       <option value="<?= $pet['pet_id'] ?>"><?= htmlspecialchars($pet['name']) ?></option>
                       <?php endwhile; ?>
                       
                    </select>

                    <label>Date</label>
                    <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" onchange="fetchSlots(this, <?= $row['vet_id'] ?>, this.form)" required>
                    
                    <label>Time</label>
                    <select name="appointment_time" required>
                        <option>Select a date first</option>
                    </select>

                    <label>Notes</label>
                    <textarea name="notes" rows="2" placeholder="Optional"></textarea>

                    <button type="submit">Book Appointment</button>
                </form>
            </div>
            <img src="images/vet_service.jpg" alt="Vet Service">
        </div>
    <?php endforeach; ?>
</div>

<script>
function fetchSlots(dateInput, vetId, form) {
    const date = dateInput.value;
    const timeSelect = form.querySelector('select[name="appointment_time"]');
    if (!date) return;

    // Clear previous options
    timeSelect.innerHTML = '';

    fetch(`get_slots.php?date=${date}&vet_id=${vetId}`)
        .then(res => res.json())
        .then(data => {
            // Normalize booked times
            const bookedTimes = data.map(t => t.trim().slice(0, 5)); // Convert "09:00:00" to "09:00"
            const allTimes = Array.from({ length: 9 }, (_, i) => {
                const hour = (i + 9).toString().padStart(2, '0');
                return `${hour}:00`;
            });

            let added = false;
            allTimes.forEach(t => {
                if (!bookedTimes.includes(t)) {
                    const option = document.createElement('option');
                    option.value = `${t}:00`; // Formatted back to full "HH:MM:SS"
                    option.text = new Date(`1970-01-01T${t}:00`).toLocaleTimeString([], {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    timeSelect.appendChild(option);
                    added = true;
                }
            });

            if (!added) {
                const option = document.createElement('option');
                option.text = 'No slots available';
                option.disabled = true;
                timeSelect.appendChild(option);
            }
        })
        .catch(err => {
            console.error('Error fetching slots:', err);
            const option = document.createElement('option');
            option.text = 'Error loading slots';
            option.disabled = true;
            timeSelect.appendChild(option);
        });
}

const toggle = document.getElementById('menu-toggle');
const nav = document.getElementById('nav_bar');
toggle.addEventListener('click', () => {
    nav.classList.toggle('show');
});
</script>

</body>
</html>
