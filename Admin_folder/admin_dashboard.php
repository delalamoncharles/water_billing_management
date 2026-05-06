<?php
session_start();
session_regenerate_id(true);
include '../database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch total users
$user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Fetch all bills
$bills = [];
$result = $conn->query("
    SELECT bills.*, users.username 
    FROM bills 
    JOIN users ON bills.user_id = users.id
    ORDER BY billing_month ASC
");

while ($row = $result->fetch_assoc()) {
    $bills[] = $row;
}

// Prepare data
$months = [];
$amounts = [];
$usage = [];
$paid = 0;
$unpaid = 0;
$total_amount = 0;

foreach ($bills as $bill) {
    $months[] = $bill['billing_month'];
    $amounts[] = $bill['amount'];
    $usage[] = $bill['usage'];

    $total_amount += $bill['amount'];

    if ($bill['status'] === 'Paid') $paid++;
    else $unpaid++;
}

$months_json = json_encode($months);
$amounts_json = json_encode($amounts);
$usage_json = json_encode($usage);
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* SAME DESIGN (copied from user dashboard) */
:root {
    --sidebar-width: 270px;
}

body {
    font-family: Arial;
    margin: 0;
    background: #e0f7fa;
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

.grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
}

.chart-box {
    background: white;
    padding: 20px;
    margin-top: 20px;
    border-radius: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
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

.paid { color: green; }
.unpaid { color: red; }
</style>
</head>

<body>

<div class="sidebar">
    <h2>💧Water Billing System</h2>

    <a href="admin_dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="manage_bills.php">📄 Manage Bills</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="../logout.php">🚪 Logout</a>
    </div>
</div>

<header>
    <h2>Admin Dashboard</h2>
</header>

<div class="container">

<!-- CARDS -->
<div class="grid">
    <div class="card">
        <h3>👥 Total Users</h3>
        <h1><?= $user_count ?></h1>
    </div>

    <div class="card">
        <h3>💰 Total Revenue</h3>
        <h1>₱<?= number_format($total_amount,2) ?></h1>
    </div>

    <div class="card">
        <h3>⚠️ Unpaid Bills</h3>
        <h1><?= $unpaid ?></h1>
    </div>

    <div class="card">
        <h3>💧 Total Usage</h3>
        <h1><?= array_sum($usage) ?> m³</h1>
    </div>
</div>

<!-- ALERT -->
<div class="chart-box" style="border-left: 5px solid red;">
    <h3>🚨 System Alerts</h3>

    <?php if ($unpaid > 0): ?>
        <p style="color:red;">⚠️ There are <?= $unpaid ?> unpaid bills.</p>
    <?php else: ?>
        <p style="color:green;">✅ All users are paid.</p>
    <?php endif; ?>
</div>

<!-- CHARTS -->
<div class="grid">
    <div class="chart-box">
        <h3>💰 Revenue Trend</h3>
        <canvas id="amountChart"></canvas>
    </div>

    <div class="chart-box">
        <h3>🚿 Usage Trend</h3>
        <canvas id="usageChart"></canvas>
    </div>
</div>

<div class="chart-box">
    <h3>📊 Payment Status</h3>
    <canvas id="statusChart"></canvas>
</div>

<!-- TABLE -->
<h2>📋 All Billing Records</h2>

<table>
<tr>
    <th>User</th>
    <th>Month</th>
    <th>Usage</th>
    <th>Amount</th>
    <th>Status</th>
</tr>

<?php foreach ($bills as $bill): ?>
<tr>
    <td><?= $bill['username'] ?></td>
    <td><?= $bill['billing_month'] ?></td>
    <td><?= $bill['usage'] ?></td>
    <td>₱<?= number_format($bill['amount'],2) ?></td>
    <td class="<?= strtolower($bill['status']) ?>">
        <?= $bill['status'] ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

</div>

<script>
const months = <?= $months_json ?>;
const amounts = <?= $amounts_json ?>;
const usage = <?= $usage_json ?>;

new Chart(document.getElementById('amountChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Revenue',
            data: amounts,
            backgroundColor: '#0288d1'
        }]
    }
});

new Chart(document.getElementById('usageChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Usage',
            data: usage,
            borderColor: 'green'
        }]
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Paid', 'Unpaid'],
        datasets: [{
            data: [<?= $paid ?>, <?= $unpaid ?>],
            backgroundColor: ['green', 'red']
        }]
    }
});
</script>

</body>
</html>