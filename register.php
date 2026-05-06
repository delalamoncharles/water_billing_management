<?php
session_start();
include 'database.php';

$error = '';

if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $error = "Email already exists!";
    } else {

        // role logic (keep your original logic)
        $adminCheck = $conn->query("SELECT * FROM users WHERE role='Admin'");
        $role = ($adminCheck->num_rows == 0) ? "Admin" : "User";

        // hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // generate verification code (added from your code)
        $code = rand(100000, 999999);

        // insert user (updated to include username + verification code fields)
        $stmt = $conn->prepare("
            INSERT INTO users (username, email, password, role, verification_code, is_verified)
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        $stmt->bind_param("sssss", $username, $email, $hashedPassword, $role, $code);

        if ($stmt->execute()) {

            // keep your OTP system (unchanged)
            $otp = rand(100000, 999999);

            $insertOtp = $conn->prepare("INSERT INTO password_resets (email, otp) VALUES (?, ?)");
            $insertOtp->bind_param("ss", $email, $otp);
            $insertOtp->execute();

            // send email verification code (added from your code)
            $subject = "Your Verification Code";
            $msg = "Hello $username, your verification code is: $code";
            $headers = "From: no-reply@yourwebsite.com";

            mail($email, $subject, $msg, $headers);

            $_SESSION['verify_email'] = $email;
            $_SESSION['demo_otp'] = $otp;
$stmt->execute();

// SEND EMAIL
$subject = "Your Verification Code";
$msg = "Hello $username, your verification code is: $code";

mail($email, $subject, $msg, $headers);

/* ✅ ADD THIS RIGHT HERE */
header("Location: verify.php?email=" . urlencode($email));
exit();

        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Water Billing System</title>

    <style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .form-container {
        background: rgba(255, 255, 255, 0.95);
        padding: 35px;
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        width: 340px;
        text-align: center;
        backdrop-filter: blur(6px);
    }

    h2 {
        margin-bottom: 20px;
        color: #01579b;
    }

    input, .btn {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 10px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input {
        border: 1px solid #b2ebf2;
        outline: none;
        transition: 0.2s;
    }

    input:focus {
        border-color: #0288d1;
        box-shadow: 0 0 5px rgba(2,136,209,0.3);
    }

    .btn {
        background: #0288d1;
        color: white;
        border: none;
        cursor: pointer;
        font-weight: bold;
        transition: 0.2s;
    }

    .btn:hover {
        background: #01579b;
        transform: translateY(-1px);
    }

    .error {
        color: #d32f2f;
        margin-bottom: 10px;
        font-size: 14px;
    }

    .bottom-box {
        margin-top: 18px;
        padding: 12px;
        background: #f1faff;
        border-radius: 10px;
        font-size: 14px;
        color: #333;
    }

    .bottom-box a {
        color: #0288d1;
        font-weight: bold;
        text-decoration: none;
    }

    .bottom-box a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>

<div class="form-container">

    <h2>Create Account</h2>

    <?php if ($error != ''): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email Account" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="register" class="btn">Register</button>
    </form>

    <div class="bottom-box">
        Already have an account?<br>
        <a href="login.php">Login here</a>
    </div>

</div>

</body>
</html>