<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include("../User/connection.php");
session_start();

$message = "";

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Function to generate random password
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    // Fetch actual email of the logged-in user from DB
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($actual_email);
    $stmt->fetch();
    $stmt->close();

    if ($email !== $actual_email) {
        $message = "❌ You can only reset your own password.";
    } else {
        $_SESSION['reset_email'] = $email;

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $new_password_plain = generateRandomPassword();

            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $new_password_plain, $email);
            $update_stmt->execute();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'shamirali9779@gmail.com';
                $mail->Password   = 'aqrwcxfehyjeoxrr';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('shamirali9779@gmail.com', 'PetSphere');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Your PetSphere New Password';
                $mail->Body    = "
                    <h3>Password Reset Successful</h3>
                    <p>Your new temporary password is:</p>
                    <p style='font-size: 18px; font-weight: bold;'>$new_password_plain</p>
                    <p>Please log in with this password and change it after logging in.</p>
                ";

                $mail->send();
                $_SESSION['reset_success'] = "✅ A new password has been sent to your email.";
                header("Location: new_password.php");
                exit;
            } catch (Exception $e) {
                $message = "❌ Mail error: " . $mail->ErrorInfo;
            }
        } else {
            $message = "❌ No account found with that email.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            padding: 40px 20px;
        }
        .container {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #0077cc;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            margin-top: 20px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005fa3;
        }
        .message {
            text-align: center;
            margin-top: 15px;
            color: #333;
        }
        .fade-out {
            animation: fadeOut 4s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-unlock-alt"></i> Reset Password</h2>
    <?php if (!empty($message)): ?>
        <div class="message fade-out"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <label for="email">Enter your email</label>
        <input type="email" name="email" id="email" required />
        <button type="submit"><i class="fas fa-key"></i> Send New Password</button>
    </form>
</div>

<script>
    const msg = document.querySelector('.message');
    if (msg) {
        msg.classList.add('fade-out');
    }
</script>
</body>
</html>
