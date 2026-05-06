<?php
session_start();
include '../database.php'; // <-- ADD THIS LINE

// Only allow Users
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: login.php");
    exit();
}

// Now $conn is defined and you can use prepare() etc.
$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_id = $user['id'];

// Fetch bills
$bills = [];

$stmt = $conn->prepare("SELECT * FROM bills WHERE user_id=? ORDER BY billing_month ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

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
<title>Dashboard - Water Billing</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
    --sidebar-width: 270px;
}

body {
    font-family: Arial;
    margin: 0;
    background: #e0f7fa;
}

/* SIDEBAR */
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
.sidebar a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 25px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    font-weight: bold; /* 👈 ADD THIS */
    transition: 0.3s;
}

/* HEADER */
header {
    background: #0288d1;
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    margin-left: var(--sidebar-width);
}

/* CONTAINER */
.container {
    margin-left: var(--sidebar-width);
    padding: 25px;
}

/* GRID */
.grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

/* CARDS */
.card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.card h3 {
    margin: 0;
    color: gray;
}

.card h1 {
    margin: 5px 0;
    color: #0288d1;
}

/* CHART BOX */
.chart-box {
    background: white;
    padding: 20px;
    margin-top: 20px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

/* TABLE */
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

tr:hover {
    background: #f1f1f1;
}

.paid { color: green; }
.unpaid { color: red; }
</style>
</head>

<body>

<div class="sidebar">
    <h2>💧 Water Billing System</h2>

    <a href="user_dashboard.php">🏠 <span>Dashboard</span></a>
    <a href="my_bills.php">📊 <span>My Bills</span></a>
    <a href="profile.php">👤 <span>Profile</span></a>
    <a href="../logout.php">🚪 <span>Logout</span></a>

</div>

<header>
    <h2>Dashboard</h2>
    
</header>

<div class="container">

<!-- CARDS -->
<div class="grid">
    <div class="card">
        <h3>💧 Total Consumption</h3>
        <h1><?= array_sum($usage) ?> m³</h1>
    </div>

    <div class="card">
        <h3>💰 Total Bills</h3>
        <h1>₱<?= number_format($total_amount,2) ?></h1>
    </div>

    <div class="card">
        <h3>⚠️ Unpaid Bills</h3>
        <h1><?= $unpaid ?></h1>
    </div>
</div>

<!-- ALERT -->
<div class="chart-box" style="border-left: 5px solid red;">
    <h3>🚨 Alerts</h3>

    <?php if ($unpaid > 0): ?>
        <p style="color:red;">⚠️ You have <?= $unpaid ?> unpaid bill(s).</p>
    <?php else: ?>
        <p style="color:green;">✅ All bills are paid.</p>
    <?php endif; ?>
</div>

<!-- CHARTS -->
<div class="grid">
    <div class="chart-box">
        <h3>💰 Billing Trend</h3>
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
<h2 id="bills">📋 Recent Billing Activity</h2>

<?php if (count($bills) > 0): ?>
<table>
<tr>
    <th>ID</th>
    <th>Month</th>
    <th>Usage</th>
    <th>Amount</th>
    <th>Status</th>
</tr>

<?php foreach ($bills as $bill): ?>
<tr>
    <td><?= $bill['id'] ?></td>
    <td><?= $bill['billing_month'] ?></td>
    <td><?= $bill['usage'] ?></td>
    <td>₱<?= number_format($bill['amount'],2) ?></td>
    <td class="<?= strtolower($bill['status']) ?>">
        <?= $bill['status'] ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

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
            label: 'Billing Amount',
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
            label: 'Water Usage',
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