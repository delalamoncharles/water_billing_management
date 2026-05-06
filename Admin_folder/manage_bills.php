<?php
session_start();
session_regenerate_id(true);
include '../database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// RATE PER CUBIC METER (you can change this)
$rate = 20;

// ADD BILL
if(isset($_POST['add_bill'])){
    $user_id = $_POST['user_id'];
    $month = $_POST['billing_month'];
    $usage = $_POST['usage'];

    $amount = $usage * $rate;

    // ✅ AUTO CREATE DUE DATE (last day of selected month)
    $due_date = date("Y-m-t", strtotime($month));

    $stmt = $conn->prepare("INSERT INTO bills (user_id, billing_month, `usage`, amount, status, due_date) VALUES (?, ?, ?, ?, 'Unpaid', ?)");
    $stmt->bind_param("isids", $user_id, $month, $usage, $amount, $due_date);
    $stmt->execute();
}
// DELETE BILL
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $conn->query("DELETE FROM bills WHERE id=$id");
}

// UPDATE BILL
// UPDATE BILL
if(isset($_POST['update_bill'])){
    $id = $_POST['id'];
    $usage = $_POST['usage'];
    $status = $_POST['status'];

    $amount = $usage * $rate;

    $stmt = $conn->prepare("
        UPDATE bills 
        SET `usage`=?, amount=?, status=?
        WHERE id=?
    ");
    $stmt->bind_param("idsi", $usage, $amount, $status, $id);
    $stmt->execute();
}

// FETCH USERS
$users = $conn->query("SELECT * FROM users");

// FETCH BILLS
$bills = $conn->query("
    SELECT 
        users.id AS user_id,
        users.username,
        bills.id AS bill_id,
        bills.billing_month,
        bills.usage,
        bills.amount,
        bills.status
    FROM users
    LEFT JOIN bills ON bills.user_id = users.id
    ORDER BY users.id DESC
");?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Bills</title>

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

input, select {
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
    <h2>Manage Bills</h2>
    <a href="logout.php" style="color:white;">Logout</a>
</header>

<div class="container">

<!-- ADD BILL -->
<div class="card">
    <h3>➕ Add New Bill</h3>

    <form method="POST">
        <select name="user_id" required>
            <option value="">Select User</option>
            <?php while($u = $users->fetch_assoc()): ?>
                <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
            <?php endwhile; ?>
        </select>

        <input type="month" name="billing_month" required>
        <input type="number" name="usage" placeholder="Water Usage (m³)" required>

        <button type="submit" class="btn add" name="add_bill">Add Bill</button>
    </form>
</div>

<!-- BILL TABLE -->
<div class="card">
    <h3>📄 Billing Records</h3>

    <table>
    <tr>
        <th>User</th>
        <th>Month</th>
        <th>Usage</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php while($row = $bills->fetch_assoc()): ?>
    <tr>
        <form method="POST">
        <td><?= $row['username'] ?></td>

        <td><?= $row['billing_month'] ?? 'No bill yet' ?></td>

        <td>
            <input type="number" name="usage" value="<?= $row['usage'] ?? 0 ?>">
        </td>

        <td>
            ₱<?= isset($row['amount']) ? number_format($row['amount'],2) : '0.00' ?>
        </td>

        <td>
            <select name="status">
                <option <?= ($row['status'] ?? '')=='Paid'?'selected':'' ?>>Paid</option>
                <option <?= ($row['status'] ?? '')=='Unpaid'?'selected':'' ?>>Unpaid</option>
            </select>
        </td>

        <td>
            <input type="hidden" name="id" value="<?= $row['bill_id'] ?>">

            <?php if($row['bill_id']): ?>
                <button class="btn edit" name="update_bill">Update</button>

                <a href="?delete=<?= $row['bill_id'] ?>" onclick="return confirm('Delete bill?')">
                    <button type="button" class="btn delete">Delete</button>
                </a>
            <?php else: ?>
                <span style="color:gray;">No bill</span>
            <?php endif; ?>
        </td>

        </form>
    </tr>
    <?php endwhile; ?>
</table>
</div>

</div>

</body>
</html>