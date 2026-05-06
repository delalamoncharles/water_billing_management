<?php
session_start();
session_regenerate_id(true);
include '../database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// ADD USER (ROLE REMOVED → default = User)
if(isset($_POST['add_user'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $role = "User";

    $conn->query("INSERT INTO users (username,password,role) 
                  VALUES ('$username','$password','$role')");
}

// DELETE USER
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$id");
}

// UPDATE USER (ROLE REMOVED + PASSWORD OPTION)
if(isset($_POST['update_user'])){
    $id = $_POST['id'];
    $username = $_POST['username'];

    // check if password fields exist
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // If password is NOT empty → update it
    if(!empty($password)){
        if($password !== $confirm_password){
            echo "<script>alert('Passwords do not match!');</script>";
        } else {
            $hashed_password = md5($password); // KEEPING YOUR ORIGINAL SYSTEM

            $conn->query("UPDATE users 
                          SET username='$username', password='$hashed_password'
                          WHERE id=$id");
        }
    } else {
        // Update only username if password is empty
        $conn->query("UPDATE users 
                      SET username='$username'
                      WHERE id=$id");
    }
}

// FETCH USERS
$users = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Users</title>

<style>
:root {
    --sidebar-width: 270px;
}

body {
    font-family: Arial;
    margin: 0;
    background: #e0f7fa;
}

header {
    background: #0288d1;
    color: white;
    padding: 15px;
    margin-left: var(--sidebar-width);
    display: flex;
    justify-content: space-between;
}

.container {
    margin-left: var(--sidebar-width);
    padding: 25px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th, td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: center;
}

th {
    background: #0288d1;
    color: white;
}

/* BUTTONS */
.btn {
    padding: 6px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.add { background: green; color: white; }
.edit { background: orange; color: white; }
.delete { background: red; color: white; }

input {
    padding: 8px;
    margin: 5px;
}
.sidebar {
    position: fixed;
    width: var(--sidebar-width);
    height: 100%;
    background: linear-gradient(180deg, #01579b, #0288d1);
    color: white;
    display: flex;
    flex-direction: column;
    padding-top: 25px;
}

.sidebar {
    position: fixed;
    width: var(--sidebar-width);
    height: 100%;
    background: linear-gradient(180deg, #01579b, #0288d1);
    color: white;
    display: flex;
    flex-direction: column;
    padding-top: 25px;
    box-shadow: 4px 0 15px rgba(0,0,0,0.3);
}

/* TITLE */
.sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 22px;
    letter-spacing: 1px;
}

/* LINKS */
.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 25px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    position: relative;
    transition: all 0.3s ease;
}

/* HOVER EFFECT (SMOOTH MOTION) */
.sidebar a:hover {
    background: rgba(255,255,255,0.15);
    padding-left: 35px;
}

/* LEFT INDICATOR BAR */
.sidebar a::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%) scaleY(0);
    width: 5px;
    height: 60%;
    background: #00e5ff;
    border-radius: 0 5px 5px 0;
    transition: 0.3s;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100%;
    background: linear-gradient(180deg, #01579b, #0288d1);
    color: white;
    display: flex;
    flex-direction: column;
    padding-top: 25px;
    box-shadow: 4px 0 15px rgba(0,0,0,0.3);
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 22px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 25px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    transition: 0.3s;
}

.sidebar a:hover {
    background: rgba(255,255,255,0.2);
    padding-left: 30px;
}

.sidebar-footer {
    margin-top: auto;
    padding: 10px;
}

</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>💧Water Billing System</h2>

    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_bills.php">📄 Manage Bills</a>
    <a href="settings.php">⚙️ Settings</a>

    <a href="../logout.php">🚪 Logout</a>
    </div>
</div>

<!-- HEADER -->
<header>
    <h2>Manage Users</h2>
    <a href="logout.php" style="color:white;">Logout</a>
</header>

<div class="container">

<!-- ADD USER -->
<div class="card">
    <h3>➕ Add New User</h3>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <button class="btn add" name="add_user">Add User</button>
    </form>
</div>

<!-- USER TABLE -->
<div class="card">
    <h3>👥 User List</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Action</th>
        </tr>

        <?php while($row = $users->fetch_assoc()): ?>
        <tr>
    <td><?= $row['id']; ?></td>

    <form method="POST">
    <td>
        <input type="text" name="username" value="<?= $row['username']; ?>" required>
    </td>

    <td>
        <!-- NEW PASSWORD FIELDS -->
        <input type="password" name="password" placeholder="New Password">
        <input type="password" name="confirm_password" placeholder="Confirm Password">

        <input type="hidden" name="id" value="<?= $row['id']; ?>">

        <button class="btn edit" name="update_user">Update</button>

        <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete user?')">
            <button type="button" class="btn delete">Delete</button>
        </a>
    </td>
    </form>
</tr>
        <?php endwhile; ?>
    </table>
</div>

</div>

</body>
</html>