<?php
session_start();
include 'database.php';

// Check if user is logged in and is a User
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}

// Get user info
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Get bill_id from URL
if (!isset($_GET['bill_id']) || !is_numeric($_GET['bill_id'])) {
    header("Location: user_dashboard.php");
    exit();
}
$bill_id = intval($_GET['bill_id']);

// Verify the bill belongs to this user and is unpaid
$stmt = $conn->prepare("SELECT * FROM bills WHERE id=? AND user_id=? AND status='Unpaid'");
$stmt->bind_param("ii", $bill_id, $user_id);
$stmt->execute();
$bill_result = $stmt->get_result();

if ($bill_result->num_rows === 0) {
    // Either bill doesn't exist or is already paid
    $_SESSION['payment_error'] = "This bill cannot be paid.";
    header("Location: user_dashboard.php");
    exit();
}

// Update bill status to Paid
$stmt = $conn->prepare("UPDATE bills SET status='Paid' WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $bill_id, $user_id);
$stmt->execute();

$_SESSION['payment_success'] = "Payment successful for Bill ID: $bill_id";
header("Location: user_dashboard.php");
exit();