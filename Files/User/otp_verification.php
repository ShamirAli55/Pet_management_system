<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();
include("connection.php");

$message = "";

if (!isset($_SESSION['email']) || !isset($_SESSION['otp'])) {
    header("Location: sign_up.php");
    exit;
}

$email = $_SESSION['email'];
$otp = $_SESSION['otp'];

if (!isset($_SESSION['otp_sent'])) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'shamirali9779@gmail.com';
        $mail->Password   = 'aqrwcxfehyjeoxrr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('shamirali9779@gmail.com', 'Pet Store');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body    = "Your OTP code is: <strong>$otp</strong>";

        $mail->send();
        $_SESSION['otp_sent'] = true;
        $message = "✅ OTP has been sent to your email: <strong>$email</strong>";
    } catch (Exception $e) {
        $message = "❌ Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    $entered_otp = trim($_POST['otp']);

    if ($entered_otp == $_SESSION['otp']) {
        $name     = mysqli_real_escape_string($conn, $_SESSION['username']);
        $email    = mysqli_real_escape_string($conn, $_SESSION['email']);
        $password = $_SESSION['password'];

        $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $message = "❌ This email is already registered. Please login instead.";
        } else {
            $insert = "INSERT INTO users (username, email, password) VALUES ('$name', '$email', '$password')";
            if (mysqli_query($conn, $insert)) {
                session_unset();
                session_destroy();
                header("Location: login.php?registered=true");
                exit;
            } else {
                $message = "❌ Database error: " . mysqli_error($conn);
            }
        }
    } else {
        $message = "❌ Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="../User/Style/signup.css">
    <style>
        .message { color: red; }
    </style>
</head>
<body>
<a href="sign_up.php" class="back-arrow" style="display:inline-block; margin:20px;">&larr; Back</a>
<div class="container">
    <div class="login">
        <h1>OTP Verification</h1>
        <?php if ($message): ?>
            <p class="message" id="msg"><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="otp">Enter OTP:</label>
            <input type="text" name="otp" required placeholder="Enter the 6-digit OTP">
            <input type="submit" name="verify" value="Verify OTP">
        </form>
    </div>
</div>

<script>
// Hide the message after 3 seconds
setTimeout(() => {
    const msg = document.getElementById("msg");
    if (msg) msg.style.display = "none";
}, 3000);
</script>

</body>
</html>
