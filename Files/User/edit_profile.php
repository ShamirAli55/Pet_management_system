<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    if (!empty($_FILES['profile_image']['name'])) {
        $img_name = $_FILES['profile_image']['name'];
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($img_ext, $allowed_ext)) {
            $new_name = uniqid("IMG-", true) . '.' . $img_ext;
            $upload_path = __DIR__ . "/Avatars/" . $new_name;

            if (move_uploaded_file($tmp_name, $upload_path)) {
                $query = "UPDATE users SET username='$username', profile_image='Avatars/$new_name' WHERE user_id=$user_id";
            } else {
                $error = "❌ Failed to move uploaded file.";
            }
        } else {
            $error = "❌ Invalid file format. Only JPG, JPEG, PNG allowed.";
        }
    } else {
        $query = "UPDATE users SET username='$username' WHERE user_id=$user_id";
    }

    if (empty($error) && mysqli_query($conn, $query)) {
        header("Location: profile.php");
        exit;
    } elseif (empty($error)) {
        $error = "❌ Error updating profile: " . mysqli_error($conn);
    }
}

$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Get latest profile image
$profile_path = !empty($user['profile_image']) ? $user['profile_image'] : 'Avatars/default.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      padding: 40px 20px;
    }
    .form-container {
      max-width: 500px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .form-container h2 {
      text-align: center;
      margin-bottom: 25px;
    }
    label {
      display: block;
      margin-top: 15px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .profile-image {
      display: block;
      margin: 0 auto 15px auto;
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid #444;
    }
    .buttons {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }
    .buttons input, .buttons a {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      cursor: pointer;
      font-weight: 500;
    }
    .save-btn {
      background-color: #333;
      color: white;
    }
    .cancel-btn {
      background-color: #ccc;
      color: black;
    }
    .save-btn:hover {
      background-color: rgb(91, 101, 101);
    }
    .cancel-btn:hover {
      background-color: #aaa;
    }
    .error {
      color: red;
      text-align: center;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="form-container">
  <img src="<?= htmlspecialchars($profile_path) . '?t=' . time() ?>" alt="Profile Image" class="profile-image">

  <h2>Edit Profile</h2>

  <?php if (!empty($error)): ?>
    <div class="error"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <label>Email:</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

    <label>Password:</label>
<div style="display: flex; align-items: center; gap: 10px;">
  <input type="password" value="<?= htmlspecialchars($user['password']) ?>" readonly style="flex: 1;">
  <a href="change_password.php" style="padding: 10px 16px; background-color: #333; color: white; border-radius: 6px; text-decoration: none; font-weight: 500;">
    Change
  </a>
</div>


    <label>Profile Image:</label>
    <input type="file" name="profile_image" accept="image/*">

    <div class="buttons">
      <input type="submit" class="save-btn" value="Save Changes">
      <a href="profile.php" class="cancel-btn">Cancel</a>
    </div>
  </form>
</div>

</body>
</html>
