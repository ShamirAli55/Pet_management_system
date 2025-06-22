<?php
session_start();
include("../User/connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    if (!empty($_FILES['profile_image']['name'])) {
        $img_name = $_FILES['profile_image']['name'];
        $tmp_name = $_FILES['profile_image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        if (in_array($img_ext, $allowed_ext)) {
            $new_name = uniqid("IMG-", true) . '.' . $img_ext;
            $upload_path = __DIR__ . "/uploads/" . $new_name; // Absolute path
            if (move_uploaded_file($tmp_name, $upload_path)) {
                $query = "UPDATE users SET username='$username', email='$email', profile_image='$new_name' WHERE user_id=$user_id";
            } else {
                echo "<p style='color:red'>❌ Failed to move uploaded file.</p>";
                exit;
            }
        } else {
            echo "<p style='color:red'>❌ Invalid file format. Allowed: jpg, jpeg, png.</p>";
            exit;
        }
    } else {
        $query = "UPDATE users SET username='$username', email='$email' WHERE user_id=$user_id";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: profile.php");
        exit;
    } else {
        echo "<p style='color:red'>❌ Error updating profile: " . mysqli_error($conn) . "</p>";
    }
}

$query = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="email"], input[type="file"], input[type="password"] {
            width: 100%; padding: 10px; margin-top: 5px;
            border: 1px solid #ccc; border-radius: 6px;
        }
        .buttons { display: flex; justify-content: space-between; margin-top: 20px; }
        .buttons input, .buttons a {
            padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer;
            text-decoration: none; font-weight: 500;
        }
        .save-btn { background-color: #333; color: white; }
        .cancel-btn { background-color: #ccc; color: black; }
        .save-btn:hover { background-color: rgb(91, 101, 101); }
        .cancel-btn:hover { background-color: #aaa; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Profile</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Password:</label>
        <input type="password" name="password" value="<?= htmlspecialchars($user['password']) ?>" readonly>

        <label>Profile Image:</label>
        <input type="file" name="profile_image">

        <div class="buttons">
            <input type="submit" class="save-btn" value="Save Changes">
            <a href="profile.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>


