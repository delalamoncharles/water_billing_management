<?php
session_start();
include 'database.php';

$id = $_GET['id'];
$data = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $meter = $_POST['meter'];
    $amount = $_POST['amount'];

    $conn->query("UPDATE users 
                  SET name='$name', meter='$meter', amount='$amount'
                  WHERE id=$id");

    header("Location: admin_dashboard.php");
}
?>

<form method="POST">
    <input type="text" name="name" value="<?= $data['name'] ?>"><br><br>
    <input type="number" name="meter" value="<?= $data['meter'] ?>"><br><br>
    <input type="number" name="amount" value="<?= $data['amount'] ?>"><br><br>
    <button name="update">Update</button>
</form>