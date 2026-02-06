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
<title>macademia Faculty | Reset Password</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --primary: #00f5ff;
    --primary-glow: rgba(0, 245, 255, 0.5);
    --secondary: #7b2ff7;
    --accent: #f72b7b;
    --bg-main: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%);
    --bg-panel: rgba(10, 14, 39, 0.7);
    --bg-card: rgba(255, 255, 255, 0.04);
    --bg-card-border: rgba(255, 255, 255, 0.08);
    --text-primary: #f0f4f8;
    --text-secondary: #cbd5e1;
    --text-muted: #94a3b8;
    --error: #ff3b3b;
    --success: #00e676;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    --glass-blur: blur(24px) saturate(200%);
}

:root[data-theme="light"] {
    --primary: #8B5E3C;
    --primary-glow: rgba(139, 94, 60, 0.12);
    --secondary: #3B6A47;
    --accent: #A67C52;
    --bg-main: linear-gradient(180deg, #f4efe6 0%, #efe7d9 100%);
    --bg-panel: rgba(255, 255, 255, 0.9);
    --bg-card: #ffffff;
    --bg-card-border: rgba(0, 0, 0, 0.06);
    --text-primary: #2b2b2b;
    --text-secondary: #4b4b4b;
    --text-muted: #6b6b6b;
    --error: #A67B6E;
    --success: #7A9E7D;
    --shadow: 0 12px 30px rgba(15, 15, 15, 0.08);
    --transition: all 0.3s ease;
    --glass-blur: blur(8px) saturate(120%);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-main);
    background-size: 400% 400%;
    animation: gradientShift 25s ease infinite;
    color: var(--text-primary);
    min-height: 100vh;
    display: grid;
    place-items: center;
    position: relative;
    overflow-x: hidden;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    background: 
        radial-gradient(circle at 20% 30%, var(--primary-glow) 0%, transparent 35%),
        radial-gradient(circle at 80% 70%, rgba(123, 47, 247, 0.2) 0%, transparent 35%),
        radial-gradient(circle at 50% 10%, rgba(247, 43, 123, 0.12) 0%, transparent 30%);
    animation: meshFloat 30s ease-in-out infinite;
    z-index: -1;
    pointer-events: none;
}

@keyframes meshFloat {
    0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
    33% { transform: translate(-30px, -40px) scale(1.1) rotate(2deg); }
    66% { transform: translate(30px, -30px) scale(0.9) rotate(-1deg); }
}

.theme-toggle {
    position: fixed;
    top: 1.5rem;
    right: 1.5rem;
    z-index: 10;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid var(--bg-card-border);
    color: var(--text-secondary);
    padding: 0.55rem 0.85rem;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    backdrop-filter: var(--glass-blur);
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

.container {
    width: min(500px, 90vw);
    padding: 2rem 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 2rem;
    text-decoration: none;
    color: var(--text-primary);
    width: fit-content;
    transition: var(--transition);
    margin-left: auto;
    margin-right: auto;
}

.logo:hover {
    transform: translateY(-2px);
}

.logo-icon {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 14px;
    display: grid;
    place-items: center;
    font-size: 1.5rem;
    animation: logoPulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    position: relative;
}

.logo-icon::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 14px;
    opacity: 0.4;
    filter: blur(8px);
    z-index: -1;
}

@keyframes logoPulse {
    0%, 100% { transform: scale(1) rotate(0deg); box-shadow: 0 0 25px var(--primary-glow); }
    50% { transform: scale(1.05) rotate(2deg); box-shadow: 0 0 45px var(--primary-glow); }
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.form-card {
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 28px;
    padding: 3rem 2.5rem;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.form-card::before {
    content: '';
    position: absolute;
    inset: -50%;
    background: conic-gradient(from 0deg at 50% 50%, 
        transparent 0deg, 
        var(--primary-glow) 90deg, 
        transparent 180deg,
        var(--primary-glow) 270deg,
        transparent 360deg);
    animation: rotateBorder 8s linear infinite;
    opacity: 0.15;
}

@keyframes rotateBorder {
    to { transform: rotate(360deg); }
}

.form-header {
    text-align: center;
    margin-bottom: 2.5rem;
    position: relative;
    z-index: 1;
}

.form-header .icon-wrapper {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, rgba(0, 245, 255, 0.1), rgba(123, 47, 247, 0.1));
    border-radius: 20px;
    display: grid;
    place-items: center;
    position: relative;
    animation: float 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(5deg); }
}

.form-header .icon-wrapper i {
    font-size: 2.5rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    filter: drop-shadow(0 4px 12px var(--primary-glow));
}

.form-header h2 {
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 900;
    margin-bottom: 0.75rem;
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--primary) 50%, var(--accent) 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: textShimmer 3s linear infinite;
    letter-spacing: -0.5px;
}

@keyframes textShimmer {
    to { background-position: 200% center; }
}

.form-header p {
    color: var(--text-secondary);
    font-size: 1rem;
    line-height: 1.6;
}

.alert {
    padding: 1.25rem 1.5rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    font-size: 0.95rem;
    line-height: 1.6;
    position: relative;
    animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    border: 1px solid;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert i {
    font-size: 1.25rem;
}

.alert-success {
    background: rgba(0, 230, 118, 0.12);
    border-color: rgba(0, 230, 118, 0.35);
    color: var(--success);
}

.alert-danger {
    background: rgba(255, 59, 59, 0.12);
    border-color: rgba(255, 59, 59, 0.35);
    color: var(--error);
}

.alert-info {
    background: rgba(0, 245, 255, 0.12);
    border-color: rgba(0, 245, 255, 0.35);
    color: var(--primary);
}

.alert::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    border-radius: 16px 0 0 16px;
}

.alert-success::before { background: var(--success); }
.alert-danger::before { background: var(--error); }
.alert-info::before { background: var(--primary); }

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-group {
    position: relative;
    margin-bottom: 1.75rem;
}

.form-control {
    width: 100%;
    padding: 1.1rem 3.25rem 1.1rem 1.25rem;
    font-size: 1rem;
    background: rgba(15, 23, 42, 0.5);
    border: 2px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    color: var(--text-primary);
    transition: var(--transition);
    outline: none;
    font-family: inherit;
}

.form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(0, 245, 255, 0.15);
    background: rgba(15, 23, 42, 0.7);
    transform: translateY(-1px);
}

:root[data-theme="light"] .form-control {
    background: rgba(255, 255, 255, 0.9);
    border-color: rgba(0, 0, 0, 0.08);
    color: var(--text-primary);
}

:root[data-theme="light"] .form-control:focus {
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(139, 94, 60, 0.12);
}

.form-control::placeholder {
    color: transparent;
}

.form-label {
    position: absolute;
    left: 1.25rem;
    top: 1.1rem;
    color: var(--text-muted);
    font-size: 1rem;
    pointer-events: none;
    transition: var(--transition);
    background: transparent;
    padding: 0 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control:focus ~ .form-label,
.form-control:not(:placeholder-shown) ~ .form-label {
    top: -0.65rem;
    left: 0.9rem;
    font-size: 0.8rem;
    color: var(--primary);
    background: rgba(15, 23, 42, 0.95);
    border-radius: 6px;
    font-weight: 600;
}

:root[data-theme="light"] .form-control:focus ~ .form-label,
:root[data-theme="light"] .form-control:not(:placeholder-shown) ~ .form-label {
    background: #f4efe6;
}

.form-control.is-invalid {
    border-color: var(--error);
    box-shadow: 0 0 0 4px rgba(255, 59, 59, 0.15);
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 1.25rem;
    padding: 0.5rem;
    transition: var(--transition);
    z-index: 2;
}

.toggle-password:hover {
    color: var(--primary);
    transform: translateY(-50%) scale(1.1);
}

.password-strength {
    margin-top: 0.75rem;
    display: none;
}

.password-strength.visible {
    display: block;
}

.strength-label {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.strength-bar {
    height: 6px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}

.strength-fill {
    height: 100%;
    width: 0;
    transition: width 0.3s ease, background 0.3s ease;
    border-radius: 10px;
}

.strength-fill.weak { width: 33%; background: #ff3b3b; }
.strength-fill.medium { width: 66%; background: #ffa500; }
.strength-fill.strong { width: 100%; background: #00e676; }

.password-requirements {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(0, 245, 255, 0.05);
    border-radius: 12px;
    border: 1px solid rgba(0, 245, 255, 0.1);
}

.password-requirements h4 {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.requirement {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.requirement i {
    font-size: 0.75rem;
}

.requirement.met {
    color: var(--success);
}

.requirement.met i {
    color: var(--success);
}

.btn-reset {
    width: 100%;
    padding: 1.1rem 1.5rem;
    font-size: 1.05rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    border-radius: 14px;
    color: white;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 2rem;
}

.btn-reset:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 40px var(--primary-glow);
}

.btn-reset:active {
    transform: translateY(0);
}

.btn-reset::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
    opacity: 0;
}

.btn-reset:active::before {
    width: 300px;
    height: 300px;
    opacity: 1;
}

.btn-reset:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.form-footer {
    text-align: center;
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 1px solid var(--bg-card-border);
}

.form-footer p {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.form-footer a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    position: relative;
    transition: var(--transition);
}

.form-footer a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary);
    transition: var(--transition);
}

.form-footer a:hover {
    color: var(--accent);
}

.form-footer a:hover::after {
    width: 100%;
}

@media (max-width: 480px) {
    .form-card {
        padding: 2rem 1.5rem;
    }
    
    .form-header h2 {
        font-size: 1.5rem;
    }
}

.shake {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10% { transform: translateX(-10px); }
    25% { transform: translateX(10px); }
    40% { transform: translateX(-8px); }
    60% { transform: translateX(8px); }
    75% { transform: translateX(-4px); }
    90% { transform: translateX(4px); }
}
</style>
</head>
<body>
<button class="theme-toggle" id="themeToggle" title="Toggle theme">
    <i class="bi bi-moon-fill" id="themeIcon"></i>
</button>

<div class="container">
    <a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/" class="logo">
        <div class="logo-icon">
            <i class="bi bi-mortarboard-fill"></i>
        </div>
        <h1>macademia Faculty</h1>
    </a>

    <div class="form-card">
        <div class="form-header">
            <div class="icon-wrapper">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h2>Create New Password</h2>
            <p>Enter a strong password to secure your account</p>
        </div>

        <?php if($message): ?>
            <?php if(strpos($message, 'successfully') !== false): ?>
                <div class="alert alert-success" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?php echo $message; ?></span>
                </div>
                <script>
                setTimeout(() => {
                    window.location.href = '<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/login/login';
                }, 3000);
                </script>
            <?php else: ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($show_form): ?>
        <form method="POST" id="resetForm" novalidate>
            <div class="form-group">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder=" " 
                    required 
                    minlength="8"
                    autofocus>
                <label for="password" class="form-label">
                    <i class="bi bi-lock-fill"></i>
                    New Password
                </label>
                <button type="button" class="toggle-password" data-target="password">
                    <i class="bi bi-eye-fill"></i>
                </button>
                <div class="password-strength" id="passwordStrength">
                    <div class="strength-label">Password Strength: <span id="strengthText">Weak</span></div>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <input 
                    type="password" 
                    id="confirmPassword" 
                    name="confirm_password" 
                    class="form-control" 
                    placeholder=" " 
                    required>
                <label for="confirmPassword" class="form-label">
                    <i class="bi bi-shield-check"></i>
                    Confirm Password
                </label>
                <button type="button" class="toggle-password" data-target="confirmPassword">
                    <i class="bi bi-eye-fill"></i>
                </button>
            </div>

            <div class="password-requirements">
                <h4><i class="bi bi-info-circle"></i> Password Requirements</h4>
                <div class="requirement" id="req-length">
                    <i class="bi bi-circle"></i>
                    <span>At least 8 characters</span>
                </div>
                <div class="requirement" id="req-upper">
                    <i class="bi bi-circle"></i>
                    <span>One uppercase letter</span>
                </div>
                <div class="requirement" id="req-lower">
                    <i class="bi bi-circle"></i>
                    <span>One lowercase letter</span>
                </div>
                <div class="requirement" id="req-number">
                    <i class="bi bi-circle"></i>
                    <span>One number</span>
                </div>
            </div>

            <button type="submit" class="btn-reset" id="submitBtn">
                <span>Update Password</span>
                <i class="bi bi-arrow-right-circle-fill"></i>
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <p>Remember your password? <a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

<script>
// Theme Toggle
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

// Password Toggle Visibility
document.querySelectorAll('.toggle-password').forEach(button => {
    button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = this.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye-fill');
            icon.classList.add('bi-eye-slash-fill');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash-fill');
            icon.classList.add('bi-eye-fill');
        }
    });
});

// Password Strength Checker
const passwordInput = document.getElementById('password');
const strengthIndicator = document.getElementById('passwordStrength');
const strengthFill = document.getElementById('strengthFill');
const strengthText = document.getElementById('strengthText');

const requirements = {
    length: document.getElementById('req-length'),
    upper: document.getElementById('req-upper'),
    lower: document.getElementById('req-lower'),
    number: document.getElementById('req-number')
};

function checkPasswordStrength(password) {
    let strength = 0;
    const checks = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password)
    };
    
    // Update requirement indicators
    Object.keys(checks).forEach(key => {
        if (checks[key]) {
            requirements[key].classList.add('met');
            requirements[key].querySelector('i').classList.remove('bi-circle');
            requirements[key].querySelector('i').classList.add('bi-check-circle-fill');
            strength++;
        } else {
            requirements[key].classList.remove('met');
            requirements[key].querySelector('i').classList.remove('bi-check-circle-fill');
            requirements[key].querySelector('i').classList.add('bi-circle');
        }
    });
    
    // Update strength bar
    strengthFill.classList.remove('weak', 'medium', 'strong');
    if (strength <= 2) {
        strengthFill.classList.add('weak');
        strengthText.textContent = 'Weak';
        strengthText.style.color = '#ff3b3b';
    } else if (strength === 3) {
        strengthFill.classList.add('medium');
        strengthText.textContent = 'Medium';
        strengthText.style.color = '#ffa500';
    } else {
        strengthFill.classList.add('strong');
        strengthText.textContent = 'Strong';
        strengthText.style.color = '#00e676';
    }
    
    return strength === 4;
}

if (passwordInput) {
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0) {
            strengthIndicator.classList.add('visible');
            checkPasswordStrength(password);
        } else {
            strengthIndicator.classList.remove('visible');
        }
    });
}

// Form Validation
const form = document.getElementById('resetForm');
if (form) {
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        // Clear previous errors
        passwordInput.classList.remove('is-invalid', 'shake');
        confirmPasswordInput.classList.remove('is-invalid', 'shake');
        
        // Check password strength
        if (!checkPasswordStrength(password)) {
            isValid = false;
            passwordInput.classList.add('is-invalid', 'shake');
            setTimeout(() => passwordInput.classList.remove('shake'), 500);
        }
        
        // Check password match
        if (password !== confirmPassword) {
            isValid = false;
            confirmPasswordInput.classList.add('is-invalid', 'shake');
            setTimeout(() => confirmPasswordInput.classList.remove('shake'), 500);
            
            // Show error message
            const existingError = document.querySelector('.password-mismatch-error');
            if (!existingError) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-danger password-mismatch-error';
                errorDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> <span>Passwords do not match!</span>';
                confirmPasswordInput.closest('.form-group').appendChild(errorDiv);
                
                setTimeout(() => errorDiv.remove(), 4000);
            }
        }
        
        if (isValid) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Updating...</span><div class="spinner-border spinner-border-sm ms-2" role="status"></div>';
            form.submit();
        }
    });
    
    // Real-time password match validation
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value && passwordInput.value) {
            if (this.value === passwordInput.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        }
    });
}

// Auto-dismiss alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert-danger, .alert-info');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-success')) {
            alert.style.transition = 'opacity 0.5s, transform 0.5s';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-15px)';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 6000);
</script>

</body>
</html>