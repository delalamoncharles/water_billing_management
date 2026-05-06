<?php
session_start();
include '../database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['username'];

/* GET USER DATA */
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* UPDATE SETTINGS */
if(isset($_POST['update_settings'])){

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $profile_pic = $user['profile_pic'];

    /* FILE UPLOAD */
    if(!empty($_FILES['profile_pic']['name'])){
        $allowed = ['jpg','jpeg','png','gif'];
        $file_ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

        if(in_array($file_ext, $allowed)){
            $folder = "../uploads/";
            if(!is_dir($folder)) mkdir($folder, 0777, true);

            $file_name = time() . "_" . uniqid() . "." . $file_ext;
            $target = $folder . $file_name;

            if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target)){
                $profile_pic = $file_name;
            }
        }
    }

    /* UPDATE QUERY */
    if(!empty($password)){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, profile_pic=? WHERE username=?");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $profile_pic, $current_user);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, profile_pic=? WHERE username=?");
        $stmt->bind_param("ssss", $username, $email, $profile_pic, $current_user);
    }

    $stmt->execute();

    $_SESSION['username'] = $username;

    echo "<script>alert('Settings updated successfully'); window.location='settings.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Inter', sans-serif;
}

body{
    display:flex;
    min-height:100vh;
    transition:0.3s;
}

body.light{
    background:#f4f6fb;
    color:#1e1e1e;
}

body.dark{
    background:#0f172a;
    color:#fff;
}

.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    padding:25px;
}

body.light .sidebar{
    background:#ffffff;
    border-right:1px solid #e6e8ef;
}

body.dark .sidebar{
    background:#1e293b;
}

.sidebar a{
    display:block;
    padding:12px;
    margin:8px 0;
    border-radius:10px;
    text-decoration:none;
}

.sidebar a:hover{
    background:#2563eb;
    color:white;
}

.main{
    margin-left:260px;
    width:100%;
    padding:40px;
}

.card{
    max-width:900px;
    margin:auto;
    padding:35px;
    border-radius:18px;
}

body.light .card{
    background:#ffffff;
}

body.dark .card{
    background:#1e293b;
}

.profile{
    text-align:center;
}

.profile img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
}

.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
    margin-top:20px;
}

input{
    padding:12px;
    border-radius:10px;
    border:none;
}

.btn{
    padding:12px;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

.save{
    grid-column: span 2;
    background:#2563eb;
    color:white;
}

.theme{
    margin-top:15px;
    width:100%;
}

@media (max-width:768px){
    .sidebar{display:none;}
    .main{margin-left:0;}
    .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>💧Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_bills.php">Manage Bills</a>
    <a href="settings.php">Settings</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">
<div class="card">

<div class="profile">
    <img src="../uploads/<?= htmlspecialchars($user['profile_pic'] ?? 'default.png') ?>">
    <h3><?= htmlspecialchars($user['username']) ?></h3>
    <p>Administrator Account</p>
</div>

<form method="POST" enctype="multipart/form-data" class="form-grid">

    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <input type="password" name="password" placeholder="New Password (optional)">

    <input type="file" name="profile_pic">

    <button class="btn save" name="update_settings">💾 Save Changes</button>
</form>

<button type="button" id="themeToggle" class="btn theme">🌙 Dark Mode</button>

</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const theme = localStorage.getItem("theme") || "light";
    document.body.classList.add(theme);

    const toggleBtn = document.getElementById("themeToggle");

    function updateText() {
        toggleBtn.innerText = document.body.classList.contains("dark") 
            ? "☀️ Light Mode" 
            : "🌙 Dark Mode";
    }

    updateText();

    toggleBtn.addEventListener("click", function () {
        document.body.classList.toggle("dark");
        document.body.classList.toggle("light");

        const currentTheme = document.body.classList.contains("dark") ? "dark" : "light";
        localStorage.setItem("theme", currentTheme);

        updateText();
    });
});
</script>

</body>
</html>