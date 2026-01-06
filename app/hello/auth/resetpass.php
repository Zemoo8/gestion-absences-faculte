<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// expose DB connection
global $mysqli;

$message = '';
$show_form = false;

if(isset($_GET['token'])){
    $token = $_GET['token'];

    $stmt = $mysqli->prepare("SELECT user_id FROM password_resets WHERE token=?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $show_form = true;

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

            $stmt2 = $mysqli->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt2->bind_param("si", $new_pass, $user_id);
            $stmt2->execute();

            $stmt3 = $mysqli->prepare("DELETE FROM password_resets WHERE user_id=?");
            $stmt3->bind_param("i", $user_id);
            $stmt3->execute();

            $message = "Password reset successfully! You can <a href='login.php'>login</a> now.";
            $show_form = false;
        }

    } else {
        $message = "Invalid or expired token.";
    }
} else {
    $message = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | EduPortal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { 
    min-height: 100vh; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    font-family: 'Inter', sans-serif;
    background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
    color: #f8fafc;
}
.card {
    backdrop-filter: blur(12px);
    background: rgba(15,23,42,0.7);
    border-radius: 20px;
    padding: 2.5rem;
    width: 400px;
    box-shadow: 0 15px 50px rgba(0,0,0,0.6);
}
h2 { margin-bottom: 1rem; }
input.form-control {
    border-radius: 12px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.2);
    color: #f8fafc;
}
input.form-control:focus {
    border-color: #00f5ff;
    box-shadow: 0 0 10px rgba(0,245,255,0.3);
    background: rgba(15,23,42,0.6);
}
button {
    background: linear-gradient(135deg, #00f5ff, #ff0080);
    border: none;
    color: white;
    width: 100%;
    border-radius: 12px;
    padding: 0.8rem;
    font-weight: 600;
    transition: 0.3s;
}
button:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(0,245,255,0.5);}
.alert { border-radius: 12px; }
</style>
</head>
<body>

<div class="card">
    <h2>Reset Password</h2>
    <?php if($message) echo "<div class='alert alert-info'>$message</div>"; ?>
    <?php if($show_form): ?>
    <form method="POST">
        <div class="mb-3">
            <input type="password" class="form-control" name="password" placeholder="New password" required>
        </div>
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
