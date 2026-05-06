<?php
session_start();
include '../database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$current_user = $_SESSION['username'];

$stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
$stmt->bind_param("s", $current_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$message = "";
$error = "";

if (isset($_POST['update_settings'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    /* VALIDATION */
    if (empty($username) || empty($email)) {
        $error = "Username and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($password) && $password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $profile_pic = $user['profile_pic'];

        /* IMAGE UPLOAD */
        if (!empty($_FILES['profile_pic']['name'])) {

            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {

                $folder = "../uploads/";
                if (!is_dir($folder)) mkdir($folder, 0777, true);

                $file_name = time() . "_" . uniqid() . "." . $ext;
                $target = $folder . $file_name;

                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target)) {
                    $profile_pic = $file_name;
                }
            } else {
                $error = "Invalid image format.";
            }
        }

        if (!$error) {

            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=?, profile_pic=? WHERE username=?");
                $stmt->bind_param("sssss", $username, $email, $hashed, $profile_pic, $current_user);

            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, profile_pic=? WHERE username=?");
                $stmt->bind_param("ssss", $username, $email, $profile_pic, $current_user);
            }

            $stmt->execute();
            $_SESSION['username'] = $username;

            $message = "Settings updated successfully!";
        }
    }
}
?> <!DOCTYPE html>
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

/* THEMES */
body.light{ background:#f4f6fb; color:#111; }
body.dark{ background:#0f172a; color:#fff; }

/* SIDEBAR */
.sidebar{
    width:260px;
    position:fixed;
    height:100vh;
    padding:25px;
}

body.light .sidebar{ background:#fff; border-right:1px solid #e5e7eb; }
body.dark .sidebar{ background:#1e293b; }

.sidebar a{
    display:block;
    padding:12px;
    margin:8px 0;
    border-radius:10px;
    text-decoration:none;
    color:inherit;
}

.sidebar a:hover{ background:#2563eb; color:white; }

/* MAIN */
.main{
    margin-left:260px;
    width:100%;
    padding:40px;
}

/* CARD */
.card{
    max-width:900px;
    margin:auto;
    padding:35px;
    border-radius:18px;
}

body.light .card{ background:#fff; }
body.dark .card{ background:#1e293b; }

/* PROFILE */
.profile{
    text-align:center;
    margin-bottom:20px;
}

.profile img{
    width:110px;
    height:110px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #2563eb;
}

/* FORM */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

input{
    padding:12px;
    border-radius:10px;
    border:none;
    outline:none;
}

body.light input{ background:#f1f5f9; }
body.dark input{ background:#334155; color:white; }

/* BUTTON */
.btn{
    padding:12px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
}

.save{
    grid-column:span 2;
    background:#2563eb;
    color:white;
}

.theme{
    width:100%;
    margin-top:15px;
}

/* MESSAGE BOX */
.message{
    padding:12px;
    margin:10px 0;
    border-radius:10px;
    text-align:center;
}

.success{ background:#16a34a; color:white; }
.error{ background:#dc2626; color:white; }

/* IMAGE PREVIEW */
.preview{
    width:100px;
    height:100px;
    border-radius:50%;
    margin-top:10px;
    object-fit:cover;
    display:none;
}

@media(max-width:768px){
    .sidebar{display:none;}
    .main{margin-left:0;}
    .form-grid{grid-template-columns:1fr;}
}
</style>
</head>

<body>

<div class="sidebar">
    <h2>💧 Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Users</a>
    <a href="manage_bills.php">Bills</a>
    <a href="settings.php">Settings</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="main">
<div class="card">

<div class="profile">
    <img id="previewImg" src="../uploads/<?= $user['profile_pic'] ?? 'default.png' ?>">
    <h3><?= htmlspecialchars($user['username']) ?></h3>
</div>

<!-- MESSAGE -->
<?php if($message): ?>
<div class="message success"><?= $message ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="message error"><?= $error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-grid">

    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <input type="password" name="password" placeholder="New Password">

    <input type="password" name="confirm_password" placeholder="Confirm Password">

    <input type="file" name="profile_pic" onchange="previewImage(event)">

    <img id="preview" class="preview">

    <button class="btn save" name="update_settings">💾 Save Changes</button>
</form>

<button id="themeToggle" class="btn theme">🌙 Dark Mode</button>

</div>
</div>

<script>
/* THEME */
const theme = localStorage.getItem("theme") || "light";
document.body.classList.add(theme);

document.getElementById("themeToggle").onclick = function () {
    document.body.classList.toggle("dark");
    document.body.classList.toggle("light");

    localStorage.setItem("theme",
        document.body.classList.contains("dark") ? "dark" : "light"
    );

    this.innerText = document.body.classList.contains("dark")
        ? "☀️ Light Mode"
        : "🌙 Dark Mode";
};

/* IMAGE PREVIEW */
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        const img = document.getElementById("preview");
        img.src = reader.result;
        img.style.display = "block";

        document.getElementById("previewImg").src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

</body>
</html>