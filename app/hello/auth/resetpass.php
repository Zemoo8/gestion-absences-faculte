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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | EduPortal</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --primary: #00f5ff;
    --primary-glow: rgba(0, 245, 255, 0.3);
    --accent: #ff0080;
    --bg-main: linear-gradient(135deg, #0f172a, #1e293b, #334155);
    --bg-card: rgba(15,23,42,0.7);
    --text-primary: #f8fafc;
    --input-bg: rgba(255,255,255,0.05);
    --input-border: rgba(255,255,255,0.2);
    --shadow: 0 15px 50px rgba(0,0,0,0.6);
}

:root[data-theme="light"] {
    --primary: #8B5E3C;
    --primary-glow: rgba(139, 94, 60, 0.2);
    --accent: #A67C52;
    --bg-main: linear-gradient(180deg, #f4efe6 0%, #efe7d9 100%);
    --bg-card: #ffffff;
    --text-primary: #2b2b2b;
    --input-bg: #ffffff;
    --input-border: rgba(0,0,0,0.12);
    --shadow: 0 12px 30px rgba(15,15,15,0.08);
}

body { 
    min-height: 100vh; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    color: var(--text-primary);
}
.card {
    backdrop-filter: blur(12px);
    background: var(--bg-card);
    border-radius: 20px;
    padding: 2.5rem;
    width: 400px;
    box-shadow: var(--shadow);
}
h2 { margin-bottom: 1rem; }
input.form-control {
    border-radius: 12px;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    color: var(--text-primary);
}
input.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 10px var(--primary-glow);
}
button {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    color: white;
    width: 100%;
    border-radius: 12px;
    padding: 0.8rem;
    font-weight: 600;
    transition: 0.3s;
}
button:hover { transform: translateY(-2px); box-shadow: 0 10px 30px var(--primary-glow);}
.alert { border-radius: 12px; }

.theme-toggle {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 10;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid var(--input-border);
    color: var(--text-primary);
    padding: 0.55rem 0.85rem;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: 0.3s;
    backdrop-filter: blur(8px) saturate(120%);
}

.theme-toggle:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}
</style>
</head>
<body>
<button class="theme-toggle" id="themeToggle" title="Toggle theme">
    <i class="bi bi-moon-fill" id="themeIcon"></i>
</button>

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

<script>
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const root = document.documentElement;
const savedTheme = localStorage.getItem('theme');

if (savedTheme === 'light') {
    root.setAttribute('data-theme', 'light');
    themeIcon.classList.remove('bi-moon-fill');
    themeIcon.classList.add('bi-sun-fill');
} else {
    root.removeAttribute('data-theme');
    themeIcon.classList.remove('bi-sun-fill');
    themeIcon.classList.add('bi-moon-fill');
}

themeToggle.addEventListener('click', () => {
    const currentTheme = root.getAttribute('data-theme');
    if (currentTheme === 'light') {
        root.removeAttribute('data-theme');
        localStorage.setItem('theme', 'dark');
        themeIcon.classList.remove('bi-sun-fill');
        themeIcon.classList.add('bi-moon-fill');
    } else {
        root.setAttribute('data-theme', 'light');
        localStorage.setItem('theme', 'light');
        themeIcon.classList.remove('bi-moon-fill');
        themeIcon.classList.add('bi-sun-fill');
    }
});
</script>

</body>
</html>
