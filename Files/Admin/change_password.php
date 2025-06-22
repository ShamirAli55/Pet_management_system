<?php
session_start();
include("../User/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $query = "SELECT password FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (!$user || $old_password !== $user['password']) {
        $error = "Incorrect old password.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        $update = "UPDATE users SET password = '$new_password' WHERE user_id = $user_id";
        if (mysqli_query($conn, $update)) {
            $success = "Password updated successfully.";
            // header("Location: profile.php"); // optional redirect
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Change Password</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f4f4;
      padding: 40px 20px;
    }
    .form-container {
      max-width: 500px;
      background: white;
      margin: auto;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }
    label {
      display: block;
      margin: 15px 0 5px;
      color: #666;
    }
    .input-group {
      position: relative;
    }
    .input-group input {
      width: 90%;
      padding: 10px 40px 10px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 15px;
    }
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #0077cc;
      font-size: 16px;
    }
    .button-group {
      display: flex;
      justify-content: space-between;
      margin-top: 25px;
    }
    .btn {
      text-decoration: none;
      padding: 10px 18px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 15px;
      transition: background-color 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-primary {
      background-color: #0077cc;
      color: white;
    }
    .btn-primary:hover {
      background-color: #005fa3;
    }
    .btn-secondary {
      background-color: #666;
      color: white;
    }
    .btn-secondary:hover {
      background-color: #444;
    }
    .back-btn {
      display: inline-block;
      margin-bottom: 20px;
      padding: 8px 14px;
      background-color: #444;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.95rem;
      transition: background 0.3s;
    }
    .back-btn:hover {
      background-color: #222;
    }
    .message {
      text-align: center;
      margin: 15px 0;
      font-weight: 500;
    }
    .error {
      color: red;
    }
    .success {
      color: green;
    }
  </style>
</head>
<body>

<div style="max-width: 500px; margin: auto;">
  <a href="edit_profile.php" class="back-btn"><i class="fas fa-arrow-left"></i>  &nbsp;Profile</a>

  <div class="form-container">
    <h2><i class="fas fa-lock"></i> Change Password</h2>

    <?php if ($error): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="old_password">Old Password</label>
      <div class="input-group">
        <input type="password" name="old_password" id="old_password" required>
        <button type="button" class="toggle-password" onclick="togglePassword('old_password')">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <label for="new_password">New Password</label>
      <div class="input-group">
        <input type="password" name="new_password" id="new_password" required>
        <button type="button" class="toggle-password" onclick="togglePassword('new_password')">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <label for="confirm_password">Confirm New Password</label>
      <div class="input-group">
        <input type="password" name="confirm_password" id="confirm_password" required>
        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
          <i class="fas fa-eye"></i>
        </button>
      </div>

      <div class="button-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Update Password</button>
        <a href="reset_password.php" class="btn btn-secondary"><i class="fas fa-question-circle"></i> Forgot Password?</a>
      </div>
    </form>
  </div>
</div>

<script>
function togglePassword(id) {
  const input = document.getElementById(id);
  input.type = input.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
