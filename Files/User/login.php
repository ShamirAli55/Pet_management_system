<?php
    include("connection.php");
    session_start();

    $message = "";
    if (isset($_SESSION['login_msg'])) {
        $message = $_SESSION['login_msg'];
        unset($_SESSION['login_msg']); // Clear after displaying
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>   
    <link rel="stylesheet" href="../User/Style/login.css">
    <style>
        .msg-box {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            font-weight: 500;
            animation: fadeOut 0.5s ease-in-out forwards;
            animation-delay: 3s;
        }

        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body>
    <a href="index.html" class="back-arrow">&larr;</a>
    <div class="container">
        <div class="login">
            <h1>LOGIN</h1>

            <?php if (!empty($message)): ?>
                <div class="msg-box" id="msg"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="authentication.php" method="post">
                <label for="name">Name:</label>
                <input type="text" name="name" placeholder="Enter your name">

                <label for="email">Email address:</label>
                <input type="email" name="email" placeholder="Enter your email">

                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="Enter your password">

                <div class="butns">
                    <input type="submit" name="submit" value="Login">
                    <input type="submit" name="sign" value="Sign up">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
