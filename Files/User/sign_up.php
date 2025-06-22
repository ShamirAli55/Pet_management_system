<?php
include("connection.php");
session_start();

$otpSent = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $message = "This email is already registered.";
        } else {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['password'] = $password;

            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $otpSent = true;
            $message = "Your OTP is: <strong>$otp</strong>"; // Replace with PHPMailer if needed
            header("Location: otp_verification.php");
            exit;
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGN UP</title>
    <link rel="stylesheet" href="../User/Style/signup.css">
    <style>
        .msg-box {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            background-color: #e74c3c;
            text-align: center;
        }
    </style>
</head>
<body>
<a href="index.html" class="back-arrow">&larr;</a>
<div class="container">
    <div class="login">
        <h1>SIGN UP</h1>

        <?php if (!empty($message)): ?>
            <div class="msg-box" id="msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <label for="name">Name:</label>
            <input type="text" name="name" placeholder="Enter your name" required>

            <label for="email">Email address:</label>
            <input type="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>

            <input type="submit" id="create" name="create" value="Create Account">
        </form>
    </div>
</div>

<script>
// Hide message after 3 seconds
setTimeout(() => {
    const msg = document.getElementById("msg");
    if (msg) msg.style.display = "none";
}, 3000);
</script>
</body>
</html>
