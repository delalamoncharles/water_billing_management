<?php 
session_start();
session_regenerate_id(true);
include '../database.php';

if (empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) die("User not found.");

$user_id = $user['id'];

// GET FILTER VALUE
$search = $_GET['search'] ?? '';

// Build query
$params = [];
$types = "i"; // user_id
$sql = "SELECT * FROM bills WHERE user_id = ?";
$params[] = $user_id;

if (!empty($search)) {
    $search = trim($search);
    $search_lower = strtolower($search);

    $month_number = null;
    $year_number = null;

    $months = [
        'january'=>1,'jan'=>1,
        'february'=>2,'feb'=>2,
        'march'=>3,'mar'=>3,
        'april'=>4,'apr'=>4,
        'may'=>5,
        'june'=>6,'jun'=>6,
        'july'=>7,'jul'=>7,
        'august'=>8,'aug'=>8,
        'september'=>9,'sep'=>9,
        'october'=>10,'oct'=>10,
        'november'=>11,'nov'=>11,
        'december'=>12,'dec'=>12
    ];

    foreach ($months as $name => $num) {
        if (strpos($search_lower, $name) !== false) {
            $month_number = $num;
            break;
        }
    }

    if (!$month_number && preg_match('/\b(1[0-2]|0?[1-9])\b/', $search, $m)) {
        $month_number = (int)$m[0];
    }

    if (preg_match('/\b(20\d{2}|19\d{2})\b/', $search, $matches)) {
        $year_number = (int)$matches[0];
    }

    if ($month_number && $year_number) {
        $sql .= " AND MONTH(STR_TO_DATE(CONCAT(billing_month, '-01'), '%Y-%m-%d')) = ? 
                  AND YEAR(STR_TO_DATE(CONCAT(billing_month, '-01'), '%Y-%m-%d')) = ?";
        $params[] = $month_number;
        $params[] = $year_number;
        $types .= "ii";

    } elseif ($month_number) {
        $sql .= " AND MONTH(STR_TO_DATE(CONCAT(billing_month, '-01'), '%Y-%m-%d')) = ?";
        $params[] = $month_number;
        $types .= "i";

    } elseif ($year_number) {
        $sql .= " AND YEAR(STR_TO_DATE(CONCAT(billing_month, '-01'), '%Y-%m-%d')) = ?";
        $params[] = $year_number;
        $types .= "i";

    } else {
        $sql .= " AND (LOWER(status) LIKE ? OR CAST(amount AS CHAR) LIKE ?)";
        $like = "%" . strtolower($search) . "%";
        $params[] = $like;
        $params[] = $like;
        $types .= "ss";
    }
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$bills = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bills</title>

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
.filter-form {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.filter-form input {
    padding: 10px;
    width: 250px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.filter-form button {
    padding: 10px 15px;
    border: none;
    background: #0288d1;
    color: white;
    border-radius: 8px;
    cursor: pointer;
}

.filter-form button:hover {
    background: #01579b;
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
.sidebar a {
    letter-spacing: 0.5px;
}

.sidebar a:hover span {
    transform: translateX(3px);
}
.sidebar a span {
    font-weight: bold;
}
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
    <h2>📊 My Bills</h2>
</header>

<div class="container">

<form class="filter-form" method="GET">
    <input type="text" name="search" placeholder="Search status, amount, month, or year..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit">Search</button>
</form>

<div class="card">

<?php if (!empty($bills)): ?>
<table>
    <thead>
        <tr>
            <th>Amount</th>
            <th>Billing Month</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

<?php foreach ($bills as $bill): ?>
<tr>
    <td>₱<?= number_format($bill['amount'], 2) ?></td>
    <td><?= !empty($bill['billing_month']) ? date('F Y', strtotime($bill['billing_month'] . '-01')) : 'N/A' ?></td>
    <td class="<?= strtolower($bill['status']) ?>">
        <?= htmlspecialchars($bill['status']) ?>
    </td>
</tr>
<?php endforeach; ?>

    </tbody>
</table>

<?php else: ?>
    <p>No bills found</p>
<?php endif; ?>

</div>

</div>

</body>
</html>