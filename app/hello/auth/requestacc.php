<?php
// Ensure bootstrap is loaded when this view is accessed directly (preserve DB and config).
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$success = '';
$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);

    // Validation
    if(empty($nom) || empty($prenom) || empty($email)) {
        $error = "All fields are required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM account_requests WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0) {
            $error = "An account request for this email already exists. Please wait for approval.";
        } else {
            $stmt = $mysqli->prepare("INSERT INTO account_requests (nom, prenom, email, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("sss", $nom, $prenom, $email);

            if($stmt->execute()) {
                $success = "Request submitted successfully! We'll contact you at <strong>" . htmlspecialchars($email) . "</strong> within 24-48 hours with your account details.";

                // Send email notification
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'farouk.zemoo@gmail.com';
                    $mail->Password = 'kibh ehzs ofxg zpem';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty System');
                    $mail->addAddress('farouk.zemoo@gmail.com');
                    $mail->addReplyTo($email, "$nom $prenom");

                    $mail->isHTML(true);
                    $mail->Subject = 'New Faculty Account Request';
                    $admin_link = (defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost') . '/index.php/admindash';
                    $mail->Body = "
                        <h2>New Account Request</h2>
                        <p><strong>Name:</strong> " . htmlspecialchars($nom) . " " . htmlspecialchars($prenom) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Submitted:</strong> " . date('Y-m-d H:i:s') . "</p>
                        <hr>
                        <p><a href='" . $admin_link . "'>Create his account</a></p>
                    ";
                    
                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email failed: " . $mail->ErrorInfo);
                }
            } else {
                $error = "Failed to submit request. Please try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>macademia Faculty | Request Account</title>
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

    .particles {
        position: fixed;
        inset: 0;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }

    .particle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
        opacity: 0.5;
        animation: floatParticle 25s linear infinite;
    }

    .particle:nth-child(1) { width: 3px; height: 3px; top: 10%; left: 15%; animation-delay: 0s; }
    .particle:nth-child(2) { width: 5px; height: 5px; top: 70%; left: 25%; animation-delay: 5s; }
    .particle:nth-child(3) { width: 2px; height: 2px; top: 60%; left: 85%; animation-delay: 10s; }
    .particle:nth-child(4) { width: 4px; height: 4px; top: 80%; left: 75%; animation-delay: 15s; }
    .particle:nth-child(5) { width: 3px; height: 3px; top: 30%; left: 60%; animation-delay: 20s; }

    @keyframes floatParticle {
        0% { transform: translateY(100vh) translateX(0) scale(0.5); opacity: 0; }
        10% { opacity: 0.5; }
        90% { opacity: 0.5; }
        100% { transform: translateY(-100vh) translateX(50px) scale(0.3); opacity: 0; }
    }

    .container {
        width: min(1000px, 90vw);
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: center;
        min-height: 100vh;
        padding: 2rem 0;
    }

    .info-panel {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
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
    }

    .logo:hover {
        transform: translateY(-2px);
    }

    .logo-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: 14px;
        display: grid;
        place-items: center;
        font-size: 1.5rem;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); box-shadow: 0 0 20px var(--primary-glow); }
        50% { transform: scale(1.08); box-shadow: 0 0 40px var(--primary-glow); }
    }

    .logo h1 {
        font-size: 1.75rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--text-primary), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .info-title {
        font-size: clamp(1.75rem, 4vw, 2.5rem);
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.25rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .info-subtitle {
        color: var(--text-secondary);
        font-size: 1.1rem;
        line-height: 1.7;
        margin-bottom: 2.5rem;
    }

    .advantages {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .advantage {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid transparent;
    }

    .advantage:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--bg-card-border);
        transform: translateX(8px);
    }

    .advantage-icon {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, rgba(0, 245, 255, 0.1), rgba(123, 47, 247, 0.1));
        border-radius: 10px;
        display: grid;
        place-items: center;
        color: var(--primary);
        font-size: 1rem;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .advantage:hover .advantage-icon {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        transform: scale(1.1);
    }

    .advantage-content h4 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--text-primary);
    }

    .advantage-content p {
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.5;
        margin: 0;
    }

    .form-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .form-card {
        background: var(--bg-card);
        backdrop-filter: var(--glass-blur);
        border: 1px solid var(--bg-card-border);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow: var(--shadow);
        position: relative;
    }

    .form-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        opacity: 0.6;
        transition: var(--transition);
    }

    .form-card:hover::before {
        opacity: 1;
        height: 6px;
    }

    .form-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .form-header h2 {
        font-size: clamp(1.5rem, 3vw, 1.75rem);
        font-weight: 800;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .form-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 14px;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        line-height: 1.6;
        position: relative;
        animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
        border: 1px solid;
    }

    .alert-success {
        background: rgba(0, 230, 118, 0.1);
        border-color: rgba(0, 230, 118, 0.3);
        color: var(--success);
    }

    .alert-danger {
        background: rgba(255, 59, 59, 0.1);
        border-color: rgba(255, 59, 59, 0.3);
        color: var(--error);
    }

    .alert::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 14px 0 0 14px;
    }

    .alert-success::before {
        background: var(--success);
    }

    .alert-danger::before {
        background: var(--error);
    }

    .form-group {
        position: relative;
        margin-bottom: 1.75rem;
    }

    .form-control {
        width: 100%;
        padding: 1rem 1.25rem;
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
    }

    .form-control::placeholder {
        color: transparent;
    }

    .form-label {
        position: absolute;
        left: 1.25rem;
        top: 1rem;
        color: var(--text-muted);
        font-size: 1rem;
        pointer-events: none;
        transition: var(--transition);
        background: transparent;
        padding: 0 0.25rem;
    }

    .form-control:focus ~ .form-label,
    .form-control:not(:placeholder-shown) ~ .form-label {
        top: -0.6rem;
        left: 0.9rem;
        font-size: 0.8rem;
        color: var(--primary);
        background: rgba(15, 23, 42, 0.9);
        border-radius: 4px;
    }

    .form-control.is-invalid {
        border-color: var(--error);
        box-shadow: 0 0 0 4px rgba(255, 59, 59, 0.15);
    }

    .btn-submit {
        width: 100%;
        padding: 1rem 1.5rem;
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
    }

    .btn-submit:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px var(--primary-glow);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-submit::before {
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

    .btn-submit:active::before {
        width: 300px;
        height: 300px;
        opacity: 1;
    }

    .btn-submit.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-submit.loading .btn-text::after {
        content: '...';
        animation: dots 1.5s steps(4, end) infinite;
    }

    .spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    .btn-submit.loading .spinner {
        display: block;
    }

    @keyframes dots {
        0%, 20% { content: ''; }
        40% { content: '.'; }
        60% { content: '..'; }
        80%, 100% { content: '...'; }
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .form-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--bg-card-border);
    }

    .form-footer p {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
    }

    .form-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        position: relative;
    }

    .form-footer a::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 0;
        height: 1px;
        background: var(--primary);
        transition: var(--transition);
    }

    .form-footer a:hover::after {
        width: 100%;
    }

    @media (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
            gap: 2rem;
            padding: 1rem 0;
        }

        .info-panel {
            order: 2;
            text-align: center;
            padding: 1rem;
        }

        .advantage {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .form-panel {
            order: 1;
        }
    }

    @media (max-width: 480px) {
        .form-card {
            padding: 1.75rem;
        }

        .btn-submit {
            padding: 0.9rem 1.25rem;
            font-size: 0.95rem;
        }
    }

    .shake {
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        8% { transform: translateX(-10px); }
        20% { transform: translateX(10px); }
        36% { transform: translateX(-6px); }
        56% { transform: translateX(6px); }
        72% { transform: translateX(-3px); }
        88% { transform: translateX(3px); }
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    body > * {
        opacity: 0;
        animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    body > *:nth-child(1) { animation-delay: 0.1s; }
    body > *:nth-child(2) { animation-delay: 0.2s; }
    body > *:nth-child(3) { animation-delay: 0.3s; }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
</head>
<body>

<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="container">
    <div class="info-panel">
        <div class="info-header">
            <a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/" class="logo">
                <div class="logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1>macademia Faculty</h1>
            </a>
            
            <h2 class="info-title">Request Your Account</h2>
            <p class="info-subtitle">Fill out the form to request access to the faculty portal. We'll send your account credentials to the email you provide.</p>
        </div>

        <div class="advantages">
            <div class="advantage">
                <div class="advantage-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="advantage-content">
                    <h4>Secure Verification</h4>
                    <p>All requests are manually verified by our admin team</p>
                </div>
            </div>

            <div class="advantage">
                <div class="advantage-icon">
                    <i class="bi bi-envelope-open"></i>
                </div>
                <div class="advantage-content">
                    <h4>Email Delivery</h4>
                    <p>Account credentials sent directly to your inbox</p>
                </div>
            </div>

            <div class="advantage">
                <div class="advantage-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="advantage-content">
                    <h4>Fast Processing</h4>
                    <p>Most requests are approved within 24-48 hours</p>
                </div>
            </div>

            <div class="advantage">
                <div class="advantage-icon">
                    <i class="bi bi-question-circle"></i>
                </div>
                <div class="advantage-content">
                    <h4>Need Help?</h4>
                    <p><a href="mailto:farouk.zemoo@gmail.com">Contact our support team</a></p>
                </div>
            </div>
        </div>

        <div class="contact-info">
            <p><i class="bi bi-info-circle"></i> Already have an account? <a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/login/login">Sign in here</a></p>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-card">
            <div class="form-header">
                <h2>Account Request</h2>
                <p>Provide your information below</p>
            </div>

            <?php if($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 3000);
            </script>
            <?php endif; ?>

            <?php if($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="requestForm" novalidate>
                <div class="form-group">
                    <input type="text" id="nom" name="nom" class="form-control" placeholder=" " required autofocus>
                    <label for="nom" class="form-label">First Name</label>
                </div>

                <div class="form-group">
                    <input type="text" id="prenom" name="prenom" class="form-control" placeholder=" " required>
                    <label for="prenom" class="form-label">Last Name</label>
                </div>

                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder=" " required>
                    <label for="email" class="form-label">Email Address</label>
                    <small class="form-text" style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.5rem; display: block;">We'll send your account details here</small>
                </div>

                <button type="submit" class="btn-submit">
                    <span class="btn-text">Submit Request</span>

                    <div class="spinner"></div>
                </button>

            </form>

            <div class="form-footer">
                <p>Your request will be reviewed by our admin team</p>

                <p><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/login/login"><i class="bi bi-arrow-left"></i> Back to Login</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('requestForm');
    const submitBtn = form.querySelector('.btn-submit');
    
    form.addEventListener('submit', (e) => {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required]');
        
        inputs.forEach(input => {
            if(!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid', 'shake');
                setTimeout(() => {
                    input.classList.remove('shake');
                }, 500);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        // Email validation
        const emailInput = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(emailInput.value && !emailRegex.test(emailInput.value)) {
            isValid = false;
            emailInput.classList.add('is-invalid', 'shake');
            setTimeout(() => {
                emailInput.classList.remove('shake');
            }, 500);
        }

        if(!isValid) {
            e.preventDefault();
            return;
        }

        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });

    // Auto-dismiss errors
    const errorAlert = document.querySelector('.alert-danger');
    if(errorAlert) {
        setTimeout(() => {
            errorAlert.style.opacity = '0';
            errorAlert.style.transform = 'translateY(-15px)';
            setTimeout(() => errorAlert.remove(), 500);
        }, 5000);
    }

    // Input animations
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('invalid', (e) => {
            e.preventDefault();
            input.classList.add('shake');
            setTimeout(() => input.classList.remove('shake'), 500);
        });

        input.addEventListener('blur', () => {
            if(input.hasAttribute('required') && !input.value.trim()) {
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });
    });
                <script>
                setTimeout(() => {
                    window.location.href = '<?php echo defined("PUBLIC_URL") ? PUBLIC_URL : "http://localhost"; ?>/index.php/';
                }, 3000);
            </script>