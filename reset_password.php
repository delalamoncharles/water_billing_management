<?php
session_start();
include 'database.php';

$error = '';

if (isset($_POST['reset'])) {
    $otp = $_POST['otp'];
    $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email=? AND otp=?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {

        $update = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $update->bind_param("ss", $newpass, $email);
        $update->execute();

        header("Location: login.php?reset=success");
        exit();

    } else {
        $error = "Invalid OTP!";
    }
}
?>

<h2>Reset Password</h2>

<form method="POST">
    <input type="text" name="otp" placeholder="OTP" required>
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit" name="reset">Reset</button>
</form>

<p style="color:red;"><?= $error ?></p>