<?php
include 'database.php';

$message = "";
$email = $_GET['email'] ?? "";

if(isset($_POST['verify'])){

    $email = $_POST['email'];
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT verification_code FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if($user && $user['verification_code'] == $code){

        $update = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE email=?");
        $update->bind_param("s", $email);
        $update->execute();

        $message = "Account verified successfully! You can now login.";

    } else {
        $message = "Invalid verification code.";
    }
}
?>

<h2>Email Verification</h2>

<form method="POST">
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
    <input type="text" name="code" placeholder="Enter verification code" required>

    <button name="verify">Verify</button>
</form>

<p><?= $message ?></p>