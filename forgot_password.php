<?php
session_start();
include 'database.php';

$msg = '';

if (isset($_POST['send'])) {
    $email = $_POST['email'];

    $otp = rand(100000, 999999);

    $stmt = $conn->prepare("INSERT INTO password_resets (email, otp) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();

    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_otp'] = $otp;

    header("Location: reset_password.php");
    exit();
}
?>

<h2>Forgot Password</h2>

<form method="POST">
    <input type="email" name="email" placeholder="Enter Email" required>
    <button type="submit" name="send">Send OTP</button>
</form>