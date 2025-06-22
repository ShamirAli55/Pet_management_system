<?php
include("connection.php");
session_start();

if (isset($_POST['submit'])) {
    if (!empty($_POST['email']) && !empty($_POST['name']) && !empty($_POST['password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email='$email' AND username='$name'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if ($password == $user['password']) { // You can replace this with password_verify() if hashed
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../Admin/dashboard.php");
                } else {
                    header("Location: home.php");
                }
                exit();
            } else {
                $_SESSION['login_msg'] = "❌ Incorrect password.";
                sleep(2);
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['login_msg'] = "❌ Invalid email or username.";
            sleep(2);
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['login_msg'] = "❌ Please fill in all fields.";
        sleep(2);
        header("Location: login.php");
        exit();
    }
} else if (isset($_POST['sign'])) {
    header("Location: sign_up.php");
    exit();
}
