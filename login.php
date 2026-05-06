<?php
session_start();
include 'database.php';

$error = '';

// ================================
// ✅ CREATE DEFAULT ADMIN (FIXED)
// ================================
$check = $conn->prepare("SELECT id FROM users WHERE role='Admin' LIMIT 1");
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {

    $adminEmail = 'admin@gmail.com';
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $role = 'Admin';

    $insert = $conn->prepare("
        INSERT INTO users (username, email, password, role)
        VALUES (?, ?, ?, ?)
    ");
    $insert->bind_param("sss" , $adminEmail, $adminPassword, $role);
    $insert->execute();
}

// ================================
// ✅ LOGIN HANDLER (EMAIL OR USERNAME)
// ================================
if (isset($_POST['login'])) {

    $loginInput = trim($_POST['login_input']); // can be email OR username
    $password = $_POST['password'];

    // check by email OR username
    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE email=? OR username=? 
        LIMIT 1
    ");
    $stmt->bind_param("ss", $loginInput, $loginInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // OPTIONAL verification check (keeps your system safe)
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                $error = "Please verify your email first!";
            } else {

                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                session_regenerate_id(true);

                if ($user['role'] === 'Admin') {
                    header("Location: Admin_folder/admin_dashboard.php");
                } else {
                    header("Location: User_folder/user_dashboard.php");
                }
                exit();
            }

        } else {
            $error = "Incorrect password!";
        }

    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Water Billing System</title>

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

    <h2>Login Account</h2>

    <?php if ($error != ''): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="login_input" placeholder="Email or Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit" name="login" class="btn">Login</button>
    </form>

    <div class="bottom-box">
        Don’t have an account yet?<br>
        <a href="register.php">Create your account here</a>
    </div>

</div>

</body>
</html>