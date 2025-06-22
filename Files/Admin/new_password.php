<?php
session_start();
$message = isset($_SESSION['reset_success']) ? $_SESSION['reset_success'] : "You can now log in.";
unset($_SESSION['reset_success']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Complete</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #eef;
            padding: 40px;
        }
        .box {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            background-color: #0077cc;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
        }
        a:hover {
            background-color: #005fa3;
        }
    </style>
</head>
<body>
<div class="box">
    <h2>🔐 Password Reset</h2>
    <p><?= $message ?></p>
    <a href="../User/login.php">Login Now</a>
</div>
</body>
</html>
