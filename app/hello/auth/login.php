<?php
// Ensure bootstrap is loaded when this view is accessed directly (preserve design).
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// If the form posts directly to this file, delegate handling to the AuthController.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once CONTROLLERS_PATH . '/AuthController.php';
    $ctl = new AuthController();
    // AuthController::login will handle POST, perform redirects or render view.
    $ctl->login();
    // Stop further rendering by this file because controller already output or redirected.
    return;
}

// Authentication handled in `AuthController::login()`; controller supplies `$error`.
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduPortal | Secure Authentication</title>
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
        min-height: 100vh;
        display: grid;
        place-items: center;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg-main);
        background-size: 400% 400%;
        animation: gradientShift 25s ease infinite;
        color: var(--text-primary);
        position: relative;
        overflow: hidden;
    }

    body::before {
        content: '';
        position: absolute;
        inset: 0;
        background: 
            radial-gradient(circle at 15% 25%, rgba(0, 245, 255, 0.12) 0%, transparent 35%),
            radial-gradient(circle at 85% 75%, rgba(123, 47, 247, 0.12) 0%, transparent 35%),
            radial-gradient(circle at 50% 10%, rgba(247, 43, 123, 0.08) 0%, transparent 30%);
        animation: meshFloat 30s ease-in-out infinite;
        z-index: -1;
    }

    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    @keyframes meshFloat {
        0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        33% { transform: translate(-25px, -35px) scale(1.10) rotate(2deg); }
        66% { transform: translate(25px, -25px) scale(0.90) rotate(-1deg); }
    }

    .particles {
        position: absolute;
        inset: 0;
        z-index: -1;
        overflow: hidden;
    }

    .particle {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
        opacity: 0.6;
        animation: floatParticle 20s linear infinite;
    }

    .particle:nth-child(1) { width: 3px; height: 3px; top: 20%; left: 10%; animation-delay: 0s; }
    .particle:nth-child(2) { width: 5px; height: 5px; top: 80%; left: 20%; animation-delay: 4s; }
    .particle:nth-child(3) { width: 2px; height: 2px; top: 60%; left: 80%; animation-delay: 8s; }
    .particle:nth-child(4) { width: 4px; height: 4px; top: 40%; left: 70%; animation-delay: 12s; }
    .particle:nth-child(5) { width: 3px; height: 3px; top: 30%; left: 50%; animation-delay: 16s; }

    @keyframes floatParticle {
        0% { transform: translateY(100vh) translateX(0) scale(0.5); opacity: 0; }
        10% { opacity: 0.6; }
        90% { opacity: 0.6; }
        100% { transform: translateY(-100vh) translateX(50px) scale(0.3); opacity: 0; }
    }

    .container {
        width: min(1100px, 95vw);
        height: min(700px, 90vh);
        display: grid;
        grid-template-columns: 1fr 1fr;
        border-radius: 28px;
        box-shadow: var(--shadow);
        overflow: hidden;
        position: relative;
        z-index: 1;
        background: var(--bg-panel);
        backdrop-filter: var(--glass-blur);
        border: 1px solid var(--bg-card-border);
    }

    .welcome-panel {
        padding: 4rem 3.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .welcome-panel::after {
        content: '';
        position: absolute;
        top: -2px; left: -2px; right: -2px; bottom: -2px;
        background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
        z-index: -1;
        border-radius: 28px;
        opacity: 0.3;
        animation: borderPulse 4s ease infinite;
    }

    @keyframes borderPulse {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.6; }
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 3rem;
        animation: brandFloat 3s ease-in-out infinite;
    }

    @keyframes brandFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .brand-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.5rem;
        box-shadow: 0 0 25px var(--primary-glow);
    }

    .brand h1 {
        font-size: 1.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary), #a5f3fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -0.5px;
    }

    .welcome-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .welcome-title {
        font-size: clamp(2rem, 4vw, 2.75rem);
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .welcome-subtitle {
        color: var(--text-secondary);
        font-size: clamp(1rem, 1.5vw, 1.15rem);
        margin-bottom: 3rem;
        line-height: 1.7;
        font-weight: 400;
    }

    .features {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .feature {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.25rem;
        border-radius: 14px;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid transparent;
    }

    .feature:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--bg-card-border);
        transform: translateX(8px);
    }

    .feature-icon {
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, rgba(0, 245, 255, 0.1), rgba(123, 47, 247, 0.1));
        border-radius: 12px;
        display: grid;
        place-items: center;
        color: var(--primary);
        font-size: 1.25rem;
        flex-shrink: 0;
        transition: var(--transition);
    }

    .feature:hover .feature-icon {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        transform: scale(1.1);
    }

    .feature-content h3 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.35rem;
        color: var(--text-primary);
    }

    .feature-content p {
        color: var(--text-muted);
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .panel-footer {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--bg-card-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .role-indicators {
        display: flex;
        gap: 0.75rem;
    }

    .role-badge {
        padding: 0.4rem 0.8rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 20px;
        font-size: 0.75rem;
        color: var(--text-muted);
        border: 1px solid var(--bg-card-border);
        transition: var(--transition);
    }

    .role-badge:hover {
        background: rgba(0, 245, 255, 0.1);
        color: var(--primary);
        border-color: rgba(0, 245, 255, 0.3);
    }

    .login-panel {
        padding: 4rem 3.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: var(--bg-card);
        border-left: 1px solid var(--bg-card-border);
        position: relative;
    }

    .login-header {
        margin-bottom: 3rem;
    }

    .login-header h2 {
        font-size: clamp(1.5rem, 3vw, 1.75rem);
        font-weight: 700;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .login-header p {
        color: var(--text-muted);
        font-size: 0.95rem;
    }

    .error-banner {
        background: rgba(255, 59, 59, 0.12);
        border: 1px solid rgba(255, 59, 59, 0.25);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1), shake 0.6s ease-in-out;
        backdrop-filter: blur(10px);
        position: relative;
    }

    .error-banner::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--error);
        border-radius: 14px 0 0 14px;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-15px); }
        to { opacity: 1; transform: translateY(0); }
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

    .error-banner i {
        color: var(--error);
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .error-banner p {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 500;
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

    .password-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.5rem;
        transition: var(--transition);
        z-index: 2;
    }

    .password-toggle:hover {
        color: var(--primary);
    }

    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        font-size: 0.9rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        color: var(--text-muted);
        transition: var(--transition);
    }

    .remember-me:hover {
        color: var(--text-primary);
    }

    .remember-me input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .forgot-link {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        position: relative;
        transition: var(--transition);
    }

    .forgot-link::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary);
        transition: var(--transition);
    }

    .forgot-link:hover::after {
        width: 100%;
    }

    .btn-login {
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

    .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px var(--primary-glow);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .btn-login::before {
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

    .btn-login:active::before {
        width: 300px;
        height: 300px;
        opacity: 1;
    }

    .btn-login.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .btn-login.loading .btn-text::after {
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

    .btn-login.loading .spinner {
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

    .login-footer {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--bg-card-border);
    }

    .login-footer p {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
    }

    .login-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        position: relative;
        transition: var(--transition);
    }

    .login-footer a::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 1px;
        background: var(--primary);
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.3s ease;
    }

    .login-footer a:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }

    @media (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
            height: auto;
            max-height: none;
        }
        
        .welcome-panel {
            display: none;
        }
        
        .login-panel {
            padding: 3rem 2rem;
        }
    }

    @media (max-width: 480px) {
        .login-panel {
            padding: 2.5rem 1.75rem;
        }
        
        .btn-login {
            padding: 0.9rem 1.25rem;
            font-size: 0.95rem;
        }
        
        .form-options {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }

    *:focus-visible {
        outline: 3px solid var(--primary);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    .welcome-panel > * {
        opacity: 0;
        animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .brand { animation-delay: 0.1s; }
    .welcome-title { animation-delay: 0.2s; }
    .welcome-subtitle { animation-delay: 0.3s; }
    .feature:nth-child(1) { animation-delay: 0.4s; }
    .feature:nth-child(2) { animation-delay: 0.5s; }
    .feature:nth-child(3) { animation-delay: 0.6s; }
    .feature:nth-child(4) { animation-delay: 0.7s; }
    .panel-footer { animation-delay: 0.8s; }

    .login-panel > * {
        opacity: 0;
        animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    .login-panel > *:nth-child(1) { animation-delay: 0.5s; }
    .login-panel > *:nth-child(2) { animation-delay: 0.6s; }
    .login-panel > *:nth-child(3) { animation-delay: 0.7s; }
    .login-panel > *:nth-child(4) { animation-delay: 0.8s; }
    .login-panel > *:nth-child(5) { animation-delay: 0.9s; }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

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
    <div class="welcome-panel">
        <div class="brand">
            <div class="brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h1>EduPortal</h1>
        </div>
        
        <div class="welcome-content">
            <h2 class="welcome-title">Welcome to Faculty Portal</h2>
            <p class="welcome-subtitle">Access your personalized dashboard to manage courses, students, and academic resources in one secure platform.</p>
            
            <div class="features">


                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Course Management Tools</h3>
                        <p>Effortlessly manage curriculum and content</p>
                    </div>
                </div>
                
                <div class="feature">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Academic Analytics</h3>
                        <p>Deep insights into performance and engagement</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="panel-footer">
            <div class="role-indicators">
                <div class="role-badge">Admin</div>
                <div class="role-badge">Professor</div>
                <div class="role-badge">Student</div>
            </div>
            <i class="bi bi-three-dots" style="color: var(--text-muted); font-size: 1.5rem;"></i>
        </div>
    </div>

    <div class="login-panel">
        <div class="login-header">
            <h2>Sign In to Access</h2>
            <p>Enter your credentials to continue</p>
        </div>

        <?php if($error): ?>
        <div class="error-banner" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" novalidate>
            <div class="form-group">
                <input type="email" id="email" name="email" class="form-control" placeholder=" " required autocomplete="email">
                <label for="email" class="form-label">Email Address</label>
            </div>

            <div class="form-group">
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder=" " required autocomplete="current-password">
                    <label for="password" class="form-label">Password</label>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="bi bi-eye-fill"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Remember me</span>
                </label>
                    <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">
                <span class="btn-text">Sign In</span>
                <div class="spinner"></div>
            </button>
        </form>

        <div class="login-footer">
            <p>Don't have an account? <a href="requestacc.php">Request Access</a></p>

            <p><a href="index.php">← Back to Home</a></p>
        </div>
    </div>
</div>

<script>
    const toggleBtn = document.querySelector('.password-toggle');
    const passwordInput = document.getElementById('password');
    
    toggleBtn.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        toggleBtn.innerHTML = `<i class="bi bi-${type === 'password' ? 'eye-fill' : 'eye-slash-fill'}"></i>`;
    });

    const form = document.getElementById('loginForm');
    const submitBtn = form.querySelector('.btn-login');
    
    form.addEventListener('submit', (e) => {
        const email = document.getElementById('email').value.trim();
        const password = passwordInput.value;
        
        if (!email || !password) {
            e.preventDefault();
            if (!email) document.getElementById('email').classList.add('shake');
            if (!password) passwordInput.classList.add('shake');
            setTimeout(() => {
                document.getElementById('email').classList.remove('shake');
                passwordInput.classList.remove('shake');
            }, 500);
            return;
        }
        
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });

    const errorBanner = document.querySelector('.error-banner');
    if (errorBanner) {
        setTimeout(() => {
            errorBanner.style.opacity = '0';
            errorBanner.style.transform = 'translateY(-15px)';
            setTimeout(() => errorBanner.remove(), 500);
        }, 5000);
    }
    const style = document.createElement('style');
    style.textContent = `
        .shake {
            animation: shake 0.5s ease-in-out;
        }
    `;
    document.head.appendChild(style);
</script>

</body>
</html>