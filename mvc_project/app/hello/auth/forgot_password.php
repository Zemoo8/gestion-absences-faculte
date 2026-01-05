<?php
// Config loaded by bootstrap; view remains presentation-only.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Validation
    if(empty($email)) {
        $message = "Please enter your email address.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            $user = $result->fetch_assoc();
            $user_id = $user['id'];
            $token = bin2hex(random_bytes(50));

            $stmt2 = $mysqli->prepare("INSERT INTO password_resets (user_id, token, created_at) VALUES (?, ?, NOW())");
            $stmt2->bind_param("is", $user_id, $token);
            $stmt2->execute();

            $reset_link = "http://localhost/projet/Gestion-absences/login/resetpass.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'farouk.zemoo@gmail.com';
                $mail->Password = 'kibh ehzs ofxg zpem';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('farouk.zemoo@gmail.com', 'EduPortal');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <div style='font-family: Inter, sans-serif; padding: 20px;'>
                        <h2 style='color: #00f5ff;'>Password Reset Request</h2>
                        <p>Hi,</p>
                        <p>You requested a password reset for your EduPortal account.</p>
                        <p>Click the button below to reset your password:</p>
                        <a href='$reset_link' style='display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #00f5ff, #ff0080); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0;'>Reset Password</a>
                        <p>This link will expire in 1 hour for security reasons.</p>
                        <p>If you didn't request this, please ignore this email.</p>
                        <hr>
                        <p style='color: #94a3b8; font-size: 0.9rem;'>Best regards,<br>macademia Faculty Team</p>
                    </div>
                ";
                $mail->send();
                $message = "success";
            } catch (Exception $e){
                $message = "Failed to send email: " . $mail->ErrorInfo;
            }
        } else {
            $message = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>macademia Faculty | Password Recovery</title>
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
        width: min(900px, 90vw);
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

    .recovery-steps {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .step {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border-radius: 12px;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid transparent;
    }

    .step:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--bg-card-border);
        transform: translateX(8px);
    }

    .step-icon {
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

    .step:hover .step-icon {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        transform: scale(1.1);
    }

    .step-content h4 {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        color: var(--text-primary);
    }

    .step-content p {
        color: var(--text-muted);
        font-size: 0.85rem;
        line-height: 1.5;
        margin: 0;
    }

    .security-note {
        margin-top: 2rem;
        padding: 1.25rem;
        background: rgba(0, 245, 255, 0.05);
        border-radius: 14px;
        border: 1px solid rgba(0, 245, 255, 0.1);
    }

    .security-note p {
        color: var(--text-secondary);
        font-size: 0.875rem;
        line-height: 1.6;
        margin: 0;
    }

    .security-note a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        position: relative;
    }

    .security-note a::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 0;
        height: 1px;
        background: var(--primary);
        transition: var(--transition);
    }

    .security-note a:hover::after {
        width: 100%;
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

    .form-header i {
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 1rem;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
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

    .alert-info {
        background: rgba(0, 245, 255, 0.1);
        border-color: rgba(0, 245, 255, 0.3);
        color: var(--primary);
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

    .alert-info::before {
        background: var(--primary);
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

    .btn-reset {
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
    }

    .btn-reset:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px var(--primary-glow);
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

    .btn-reset.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-reset.loading .btn-text::after {
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

    .btn-reset.loading .spinner {
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

        .step {
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
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <h1>macademia Faculty</h1>
            </a>
            
            <h2 class="info-title">Password Recovery</h2>
            <p class="info-subtitle">We'll send a secure reset link to your email address. Follow the instructions to create a new password.</p>
        </div>

        <div class="recovery-steps">
            <div class="step">
                <div class="step-icon">
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="step-content">
                    <h4>Enter Your Email</h4>
                    <p>Provide your account email address</p>
                </div>
            </div>

            <div class="step">
                <div class="step-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="step-content">
                    <h4>Check Your Inbox</h4>
                    <p>We'll send a reset link immediately</p>
                </div>
            </div>

            <div class="step">
                <div class="step-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div class="step-content">
                    <h4>Reset Password</h4>
                    <p>Create a new secure password</p>
                </div>
            </div>

            <div class="step">
                <div class="step-icon">
                    <i class="bi bi-box-arrow-in-right"></i>
                </div>
                <div class="step-content">
                    <h4>Login Again</h4>
                    <p>Access your account with new credentials</p>
                </div>
            </div>
        </div>

        <div class="security-note">
            <p><i class="bi bi-info-circle"></i> For security reasons, reset links expire after 1 hour. If you don't see the email, check your spam folder or <a href="mailto:support@macademia.edu">contact support</a>.</p>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-card">
            <div class="form-header">
                <i class="bi bi-key-fill"></i>
                <h2>Reset Your Password</h2>
                <p>Enter your account email to receive a reset link</p>
            </div>

            <?php if($message === 'success'): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-send-check"></i> <strong>Reset link sent!</strong> Check your email (including spam/junk) for the password reset link.
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 4000);
            </script>
            <?php elseif($message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="resetForm" novalidate>
                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-control" placeholder=" " required autofocus>
                    <label for="email" class="form-label">Email Address</label>
                    <small class="form-text" style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.5rem; display: block;">Use the email associated with your account</small>
                </div>

                <button type="submit" class="btn-reset">
                    <span class="btn-text">Send Reset Link</span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="form-footer">
                <p>Remember your password? <a href="login.php">Back to Login</a></p>
                <p>Don't have an account? <a href="requestacc.php">Request Access</a></p>
            </div>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('resetForm');
    const submitBtn = form.querySelector('.btn-reset');
    const emailInput = document.getElementById('email');
    
    form.addEventListener('submit', (e) => {
        let isValid = true;
        
        if(!emailInput.value.trim()) {
            isValid = false;
            emailInput.classList.add('is-invalid', 'shake');
            setTimeout(() => {
                emailInput.classList.remove('shake');
            }, 500);
        } else {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(emailInput.value)) {
                isValid = false;
                emailInput.classList.add('is-invalid', 'shake');
                setTimeout(() => {
                    emailInput.classList.remove('shake');
                }, 500);
            } else {
                emailInput.classList.remove('is-invalid');
            }
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

    // Success message enhancement
    const successAlert = document.querySelector('.alert-success');
    if(successAlert) {
        successAlert.style.background = 'rgba(0, 230, 118, 0.15)';
        successAlert.style.borderColor = 'rgba(0, 230, 118, 0.4)';
        
        // Add a progress bar
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            height: 2px;
            background: linear-gradient(90deg, var(--success), transparent);
            margin-top: 0.75rem;
            border-radius: 2px;
            animation: progressBar 4s linear forwards;
        `;
        successAlert.appendChild(progressBar);
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes progressBar {
                from { width: 100%; }
                to { width: 0%; }
            }
        `;
        document.head.appendChild(style);
    }

    // Input animations
    emailInput.addEventListener('invalid', (e) => {
        e.preventDefault();
        emailInput.classList.add('shake');
        setTimeout(() => emailInput.classList.remove('shake'), 500);
    });

    emailInput.addEventListener('blur', () => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(emailInput.value && !emailRegex.test(emailInput.value)) {
            emailInput.classList.add('is-invalid');
        } else {
            emailInput.classList.remove('is-invalid');
        }
    });
</script>

</body>
</html>