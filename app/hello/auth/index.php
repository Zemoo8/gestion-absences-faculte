<header class="epic-header" id="epicHeader">
    <div class="header-container">
        <a href="#home" class="logo-holo">
            <div class="logo-icon-wrapper">
                <div class="logo-icon-pulse">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="logo-glow"></div>
            </div>
            <span class="logo-text">macademia</span>
            <span class="logo-subtitle">Faculty OS</span>
        </a>

        <!-- Navigation Links -->
        <nav class="nav-center">
            <div class="nav-links">
                <a href="#features" class="nav-link" data-section="features">
                    <span class="nav-text">Academy</span>
                    <div class="nav-underline"></div>
                </a>
                <a href="#testimonials" class="nav-link" data-section="testimonials">
                    <span class="nav-text">Legacy</span>
                    <div class="nav-underline"></div>
                </a>
                <a href="#video" class="nav-link" data-section="video">
                    <span class="nav-text">Experience</span>
                    <div class="nav-underline"></div>
                </a>
            </div>
        </nav>

        <!-- Right Side Actions -->
        <div class="header-actions">
            <!-- Notification Bell -->
            <div class="notification-wrapper">
                <button class="notification-bell" id="headerBell">
                    <i class="bi bi-bell-fill"></i>
                    <span class="notification-badge">3</span>
                </button>
                <div class="notification-dropdown" id="headerDropdown">
                    <div class="dropdown-header">
                        <strong>System Alerts</strong>
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-graph-up"></i>
                        <span>Analytics updated</span>
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-people"></i>
                        <span>12 new requests</span>
                    </div>
                    <div class="dropdown-item">
                        <i class="bi bi-shield-check"></i>
                        <span>Security check passed</span>
                    </div>
                </div>
            </div>

            <!-- Portal Access CTA -->
            <a href="/projet/Gestion-absences/public/index.php/login/login" class="portal-cta">
                <span class="cta-text">Log In</span>
                <div class="cta-shimmer"></div>
                <i class="bi bi-box-arrow-in-right"></i>
            </a>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" id="mobileToggle">
                <span class="toggle-line"></span>
                <span class="toggle-line"></span>
                <span class="toggle-line"></span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#features" class="mobile-link">Academy</a>
        <a href="#testimonials" class="mobile-link">Legacy</a>
        <a href="#video" class="mobile-link">Experience</a>
        <a href="/projet/Gestion-absences/public/index.php/login/login" class="mobile-link mobile-cta">Log In</a>
    </div>
</header>

<style>
/* === EPIC HEADER STYLES === */
:root {
    --header-height: 90px;
    --header-glass: rgba(10, 14, 26, 0.4);
    --header-border: rgba(0, 245, 255, 0.2);
}

.epic-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: var(--header-height);
    background: var(--header-glass);
    backdrop-filter: blur(25px) saturate(200%);
    border-bottom: 1px solid var(--header-border);
    z-index: 10000;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.epic-header.scrolled {
    height: 70px;
    background: rgba(10, 14, 26, 0.85);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
}

.header-container {
    width: min(1400px, 95vw);
    margin: 0 auto;
    height: 100%;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 2rem;
}

/* === HOLOGRAPHIC LOGO === */
.logo-holo {
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    position: relative;
    padding: 0.5rem;
}

.logo-icon-wrapper {
    position: relative;
    width: 50px;
    height: 50px;
}

.logo-icon-pulse {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 1.5rem;
    position: relative;
    z-index: 2;
    animation: pulse-logo 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.logo-glow {
    position: absolute;
    inset: -5px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 14px;
    filter: blur(15px);
    opacity: 0.7;
    animation: pulse-logo 2s infinite;
}

.logo-text {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 900;
    color: var(--text-primary);
    transition: all 0.3s ease;
}

.logo-subtitle {
    font-size: 0.7rem;
    color: var(--text-muted);
    font-weight: 600;
    letter-spacing: 1px;
}

.logo-holo:hover .logo-text {
    color: var(--primary);
    text-shadow: 0 0 20px var(--primary-glow);
}

@keyframes pulse-logo {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.8; }
}

/* === CENTER NAVIGATION === */
.nav-center {
    display: flex;
    justify-content: center;
}

.nav-links {
    display: flex;
    gap: 3rem;
    align-items: center;
}

.nav-link {
    position: relative;
    padding: 0.75rem 0;
    text-decoration: none;
    color: var(--text-secondary);
    font-weight: 600;
    transition: all 0.3s ease;
}

.nav-text {
    position: relative;
    z-index: 2;
}

.nav-underline {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    transition: width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.nav-link:hover {
    color: var(--primary);
}

.nav-link:hover .nav-underline {
    width: 100%;
}

.nav-link.active .nav-underline {
    width: 100%;
}

/* === HEADER ACTIONS === */
.header-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

/* Notification Bell */
.notification-wrapper {
    position: relative;
}

.notification-bell {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--bg-card-border);
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: var(--text-secondary);
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.notification-bell:hover {
    background: rgba(0, 245, 255, 0.1);
    color: var(--primary);
    border-color: var(--primary);
    transform: rotate(15deg);
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: var(--accent);
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 0.7rem;
    font-weight: 700;
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 1rem);
    right: 0;
    width: 280px;
    background: var(--bg-panel);
    border: 1px solid var(--bg-card-border);
    border-radius: 14px;
    backdrop-filter: var(--glass-blur);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    box-shadow: var(--shadow);
}

.notification-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
    color: var(--primary);
    font-weight: 700;
}

.dropdown-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.dropdown-item i {
    font-size: 1rem;
    color: var(--primary);
}

/* Portal CTA Button */
.portal-cta {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.9rem 1.75rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50px;
    text-decoration: none;
    overflow: hidden;
    transition: all 0.3s ease;
}

.cta-text {
    position: relative;
    z-index: 2;
    color: var(--bg-main);
    font-weight: 700;
    white-space: nowrap;
}

.portal-cta i {
    position: relative;
    z-index: 2;
    color: var(--bg-main);
    font-size: 1.1rem;
    transition: transform 0.3s ease;
}

.cta-shimmer {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transform: rotate(45deg);
    transition: transform 0.6s ease;
}

.portal-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 35px var(--primary-glow);
}

.portal-cta:hover .cta-shimmer {
    transform: rotate(45deg) translate(50%, 50%);
}

.portal-cta:hover i {
    transform: translateX(5px);
}

/* Mobile Toggle */
.mobile-toggle {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

.toggle-line {
    width: 25px;
    height: 3px;
    background: var(--text-primary);
    border-radius: 2px;
    transition: all 0.3s ease;
}

.mobile-toggle.active .toggle-line:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.mobile-toggle.active .toggle-line:nth-child(2) {
    opacity: 0;
}

.mobile-toggle.active .toggle-line:nth-child(3) {
    transform: rotate(-45deg) translate(5px, -5px);
}

/* Mobile Menu */
.mobile-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--bg-panel);
    backdrop-filter: var(--glass-blur);
    border-top: 1px solid var(--bg-card-border);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    transform: translateY(-100%);
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.mobile-menu.show {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
}

.mobile-link {
    padding: 0.75rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.mobile-link:hover {
    background: rgba(0, 245, 255, 0.1);
    color: var(--primary);
}

.mobile-cta {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: var(--bg-main) !important;
    text-align: center;
    margin-top: 1rem;
}

/* Scroll Progress Bar */
.scroll-progress {
    position: absolute;
    bottom: -1px;
    left: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    width: 0%;
    transition: width 0.1s ease;
}

/* Active Section Highlight */
.nav-link.active {
    color: var(--primary);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .header-container {
        grid-template-columns: auto 1fr auto;
    }
    
    .nav-center {
        display: none;
    }
    
    .mobile-toggle {
        display: flex;
    }
    
    .portal-cta {
        display: none;
    }
}

@media (max-width: 768px) {
    .header-container {
        padding: 0 1rem;
    }
    
    .logo-holo {
        gap: 0.5rem;
    }
    
    .logo-icon-wrapper {
        width: 40px;
        height: 40px;
    }
    
    .logo-text {
        font-size: 1.5rem;
    }
    
    .logo-subtitle {
        display: none;
    }
    
    .notification-wrapper {
        display: none;
    }
}
</style>

<script>
// HEADER SCROLL EFFECT
const epicHeader = document.getElementById('epicHeader');
const scrollProgress = document.createElement('div');
scrollProgress.className = 'scroll-progress';
epicHeader.appendChild(scrollProgress);

window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const maxHeight = document.documentElement.scrollHeight - window.innerHeight;
    const scrollPercent = (scrolled / maxHeight) * 100;
    
    scrollProgress.style.width = scrollPercent + '%';
    
    if (scrolled > 50) {
        epicHeader.classList.add('scrolled');
    } else {
        epicHeader.classList.remove('scrolled');
    }
});

// NOTIFICATION DROPDOWN
const headerBell = document.getElementById('headerBell');
const headerDropdown = document.getElementById('headerDropdown');

headerBell.addEventListener('click', (e) => {
    e.stopPropagation();
    headerDropdown.classList.toggle('show');
});

document.addEventListener('click', () => {
    headerDropdown.classList.remove('show');
});

// MOBILE MENU TOGGLE
const mobileToggle = document.getElementById('mobileToggle');
const mobileMenu = document.getElementById('mobileMenu');

mobileToggle.addEventListener('click', () => {
    mobileToggle.classList.toggle('active');
    mobileMenu.classList.toggle('show');
});

// ACTIVE SECTION HIGHLIGHT
const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('.nav-link');

function updateActiveSection() {
    const scrollPosition = window.pageYOffset + 100;
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');
        
        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-section') === sectionId) {
                    link.classList.add('active');
                }
            });
        }
    });
}

window.addEventListener('scroll', updateActiveSection);
updateActiveSection();

// PARTICLE EXPLOSION ON CTA (from previous)
function createExplosion(x, y) {
    const particleCount = 30;
    const explosion = document.createElement('div');
    explosion.className = 'particles-explosion';
    explosion.style.left = x + 'px';
    explosion.style.top = y + 'px';
    document.body.appendChild(explosion);
    
    for(let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle-explode';
        const angle = (Math.PI * 2 * i) / particleCount;
        const velocity = 50 + Math.random() * 50;
        particle.style.setProperty('--x', Math.cos(angle) * velocity + 'px');
        particle.style.setProperty('--y', Math.sin(angle) * velocity + 'px');
        particle.style.background = `hsl(${Math.random() * 60 + 180}, 100%, 60%)`;
        explosion.appendChild(particle);
    }
    
    setTimeout(() => explosion.remove(), 1000);
}

document.querySelectorAll('.portal-cta, .mobile-cta').forEach(button => {
    button.addEventListener('click', (e) => {
        createExplosion(e.clientX, e.clientY);
    });
});
</script>
<?php
// Bootstrap loads config and starts session; view remains presentation-only.

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT id, password, role, nom FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['nom'];

            switch($user['role']){
                case 'admin':
                    header("Location: " . PUBLIC_URL . "/index.php/admindash/dashboard"); break;
                case 'professor':
                    header("Location: " . PUBLIC_URL . "/index.php/profdash/prof_dashboard"); break;
                default:
                    header("Location: " . PUBLIC_URL . "/index.php/studdash/dashstud"); break;
            }
            exit();
        } else {
            $error = "Invalid credentials. Please verify and try again.";
        }
    } else {
        $error = "Account not found. Please check your email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>macademia Faculty Portal | Elite Academic Management</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
    :root {
        --primary: #00f5ff;
        --primary-glow: rgba(0, 245, 255, 0.5);
        --secondary: #7b2ff7;
        --accent: #f72b7b;
        --bg-main: #0a0e1a;
        --bg-panel: rgba(10, 14, 26, 0.85);
        --bg-card: rgba(20, 25, 40, 0.6);
        --bg-card-border: rgba(0, 245, 255, 0.15);
        --text-primary: #f8f5f0;
        --text-secondary: #d4c8b5;
        --text-muted: #a89e8a;
        --error: #ff3b3b;
        --success: #00e676;
        --shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.9);
        --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --glass-blur: blur(20px) saturate(180%);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background: var(--bg-main);
        color: var(--text-primary);
        overflow-x: hidden;
        cursor: none;
    }

    /* CUSTOM CURSOR */
    .cursor {
        width: 20px;
        height: 20px;
        border: 2px solid var(--primary);
        border-radius: 50%;
        position: fixed;
        pointer-events: none;
        z-index: 9999;
        transition: transform 0.1s ease;
        mix-blend-mode: difference;
    }

    .cursor-follower {
        width: 40px;
        height: 40px;
        background: rgba(0, 245, 255, 0.1);
        border-radius: 50%;
        position: fixed;
        pointer-events: none;
        z-index: 9998;
        transition: transform 0.3s ease;
    }

    /* MATRIX RAIN BACKGROUND */
    .matrix-bg {
        position: fixed;
        inset: 0;
        z-index: -2;
        opacity: 0.03;
        pointer-events: none;
    }

    /* EPIC HERO SECTION */
    .hero {
        position: relative;
        height: 100vh;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 120%;
        background: linear-gradient(rgba(10, 14, 26, 0.7), rgba(10, 14, 26, 0.9)), 
                    url('https://images.unsplash.com/photo-1495640388908-052fa64e4b8c?w=1920&h=1080&fit=crop') center/cover;
        transform: translateY(0);
        will-change: transform;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 900px;
        padding: 0 2rem;
    }

    .prestige-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(0, 245, 255, 0.1);
        border: 1px solid var(--bg-card-border);
        border-radius: 50px;
        padding: 0.75rem 1.5rem;
        margin-bottom: 2rem;
        backdrop-filter: var(--glass-blur);
        animation: pulse 3s infinite;
    }

    .prestige-badge i {
        color: var(--primary);
        font-size: 1.2rem;
        animation: spin 10s linear infinite;
    }

    .prestige-badge span {
        color: var(--primary);
        font-size: 0.875rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(3rem, 8vw, 6rem);
        font-weight: 900;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        position: relative;
    }

    /* GLITCH EFFECT */
    .hero h1::before,
    .hero h1::after {
        content: 'Forge Your Legacy';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        -webkit-text-fill-color: transparent;
    }

    .hero h1::before {
        animation: glitch-1 0.5s infinite;
        z-index: -1;
    }

    .hero h1::after {
        animation: glitch-2 0.5s infinite;
        z-index: -2;
    }

    @keyframes glitch-1 {
        0%, 100% { clip-path: inset(0 0 0 0); }
        20% { clip-path: inset(20% 0 0 0); transform: translate(-2px, 0); }
        40% { clip-path: inset(50% 0 0 0); transform: translate(2px, -2px); }
        60% { clip-path: inset(0 0 50% 0); transform: translate(-2px, 2px); }
        80% { clip-path: inset(0 0 20% 0); transform: translate(2px, 0); }
    }

    @keyframes glitch-2 {
        0%, 100% { clip-path: inset(0 0 0 0); }
        20% { clip-path: inset(0 0 20% 0); transform: translate(2px, 0); }
        40% { clip-path: inset(0 0 50% 0); transform: translate(-2px, 2px); }
        60% { clip-path: inset(50% 0 0 0); transform: translate(2px, -2px); }
        80% { clip-path: inset(20% 0 0 0); transform: translate(-2px, 0); }
    }

    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: var(--text-secondary);
        margin-bottom: 3rem;
        line-height: 1.7;
        font-weight: 400;
    }

    .typing-cursor::after {
        content: '|';
        color: var(--primary);
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 50% { opacity: 1; }
        51%, 100% { opacity: 0; }
    }

    .cta-button {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 3rem;
        background: linear-gradient(135deg, var(--primary), var(--accent), var(--secondary));
        border: none;
        border-radius: 50px;
        color: var(--bg-main);
        font-size: 1.1rem;
        font-weight: 800;
        text-decoration: none;
        cursor: none;
        transition: var(--transition);
        overflow: hidden;
        box-shadow: 0 15px 35px var(--primary-glow);
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .cta-button:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 25px 60px var(--primary-glow);
    }

    .cta-button:active {
        transform: translateY(0) scale(1);
    }

    /* PRESTIGE BANNER */
    .prestige-banner {
        background: linear-gradient(135deg, var(--bg-panel), rgba(123, 47, 247, 0.1));
        border-top: 1px solid var(--bg-card-border);
        border-bottom: 1px solid var(--bg-card-border);
        padding: 4rem 0;
        backdrop-filter: var(--glass-blur);
    }

    .prestige-container {
        width: min(1200px, 90vw);
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        text-align: center;
    }

    .prestige-item h3 {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
        text-shadow: 0 0 20px var(--primary-glow);
    }

    .prestige-item p {
        color: var(--text-muted);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* FEATURES WITH IMAGES */
    .features-section {
        padding: 8rem 0;
        position: relative;
    }

    .feature-showcase {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        margin-bottom: 6rem;
        padding: 0 2rem;
    }

    .feature-showcase:nth-child(even) .feature-image {
        order: 2;
    }

    .feature-showcase:nth-child(even) .feature-content {
        order: 1;
    }

    .feature-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2rem, 4vw, 3rem);
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .feature-content p {
        color: var(--text-secondary);
        line-height: 1.8;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    .feature-image {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        transform: perspective(1000px) rotateY(-5deg);
        transition: var(--transition);
        border: 1px solid var(--bg-card-border);
    }

    .feature-image:hover {
        transform: perspective(1000px) rotateY(0deg);
    }

    .feature-image img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .feature-image:hover img {
        transform: scale(1.05);
    }

    .feature-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(0, 245, 255, 0.1), transparent);
    }

    /* TESTIMONIALS WITH NEON */
    .testimonials-section {
        padding: 8rem 0;
        background: linear-gradient(135deg, rgba(123, 47, 247, 0.05), transparent);
    }

    .testimonials-container {
        width: min(1000px, 90vw);
        margin: 0 auto;
    }

    .testimonial-quote {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.5rem, 3vw, 2rem);
        font-style: italic;
        color: var(--text-primary);
        margin-bottom: 2rem;
        line-height: 1.4;
        position: relative;
    }

    .testimonial-quote::before,
    .testimonial-quote::after {
        content: '"';
        color: var(--primary);
        font-size: 4rem;
        position: absolute;
        opacity: 0.3;
    }

    .testimonial-quote::before {
        top: -2rem;
        left: -1rem;
    }

    .testimonial-quote::after {
        bottom: -4rem;
        right: -1rem;
    }

    /* VIDEO SECTION */
    .video-section {
        padding: 8rem 0;
        position: relative;
    }

    .video-container {
        position: relative;
        width: min(1000px, 90vw);
        margin: 0 auto;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow);
        border: 1px solid var(--bg-card-border);
    }

    .video-placeholder {
        position: relative;
        padding-bottom: 56.25%;
        background: linear-gradient(135deg, var(--bg-panel), rgba(247, 43, 123, 0.3));
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .video-placeholder img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.7;
        filter: saturate(1.2);
    }

    .play-button {
        position: absolute;
        width: 100px;
        height: 100px;
        background: rgba(0, 245, 255, 0.9);
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 3rem;
        color: var(--bg-main);
        transition: var(--transition);
        z-index: 2;
        box-shadow: 0 0 40px var(--primary-glow);
    }

    .video-placeholder:hover .play-button {
        transform: scale(1.1);
        background: var(--primary);
        box-shadow: 0 0 60px var(--primary-glow);
    }

    /* CTA SECTION - CENTERED */
    .cta-section {
        padding: 10rem 0;
        text-align: center;
        background: linear-gradient(135deg, transparent, rgba(0, 245, 255, 0.03));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .cta-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .cta-content h2 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(2.5rem, 5vw, 4rem);
        margin-bottom: 1rem;
        background: linear-gradient(135deg, var(--text-primary), var(--primary), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .cta-content p {
        color: var(--text-muted);
        font-size: 1.2rem;
        margin-bottom: 2rem;
    }

    .cta-section .cta-button {
        font-size: 1.2rem;
        padding: 1.25rem 3rem;
    }

    /* FOOTER - FIXED COLORS */
    .footer {
        padding: 3rem 0;
        background: var(--bg-panel);
        border-top: 1px solid var(--bg-card-border);
        text-align: center;
    }

    .footer-content {
        width: min(1200px, 90vw);
        margin: 0 auto;
    }

    .footer h3 {
        color: var(--primary);
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .footer p {
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .footer-links {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-top: 1rem;
        flex-wrap: wrap;
    }

    .footer-links a {
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.875rem;
        transition: var(--transition);
        position: relative;
        padding: 0.5rem 0;
    }

    .footer-links a:hover {
        color: var(--primary);
        text-shadow: 0 0 10px var(--primary-glow);
    }

    .footer-links a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background: var(--primary);
        transition: var(--transition);
    }

    .footer-links a:hover::after {
        width: 100%;
    }

    /* ANIMATED BORDER */
    .animated-border {
        position: relative;
    }

    .animated-border::before {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 20px;
        background: linear-gradient(45deg, var(--primary), var(--secondary), var(--accent), var(--primary));
        z-index: -1;
        animation: border-spin 4s linear infinite;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .animated-border:hover::before {
        opacity: 1;
    }

    @keyframes border-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* PARTICLE EXPLOSION */
    .particles-explosion {
        position: fixed;
        pointer-events: none;
        z-index: 9999;
    }

    .particle-explode {
        position: absolute;
        width: 4px;
        height: 4px;
        background: var(--primary);
        border-radius: 50%;
        animation: explode 1s ease-out forwards;
    }

    @keyframes explode {
        0% {
            transform: translate(0, 0) scale(1);
            opacity: 1;
        }
        100% {
            transform: translate(var(--x), var(--y)) scale(0);
            opacity: 0;
        }
    }

    @media (max-width: 768px) {
        .feature-showcase {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .feature-showcase:nth-child(even) .feature-image,
        .feature-showcase:nth-child(even) .feature-content {
            order: initial;
        }

        .nav-links {
            display: none;
        }
    }
</style>
</head>
<body>

<!-- Custom Cursor -->
<div class="cursor"></div>
<div class="cursor-follower"></div>

<!-- Matrix Rain -->
<canvas class="matrix-bg"></canvas>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="#" class="logo">
            <div class="logo-icon">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <span class="logo-text">macademia</span>
        </a>
        <div class="nav-links">
            <a href="#features" class="nav-link">Academy</a>
            <a href="#testimonials" class="nav-link">Legacy</a>
            <a href="#video" class="nav-link">Experience</a>
            <a href="login.php" class="nav-link nav-cta">Log In</a>
        </div>
    </div>
</nav>

<!-- Epic Hero -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="prestige-badge">
            <i class="bi bi-award-fill"></i>
            <span>Quantum-Grade Academic OS</span>
        </div>
        <h1 class="glitch-text">Forge Your Legacy</h1>
        <p class="hero-subtitle typing-cursor" id="heroSubtitle">
            Where tradition meets singularity. Experience the academic management platform that shapes tomorrow's leaders.
        </p>
        <a href="requestacc.php" class="cta-button" id="mainCta">
            <span>Initialize Journey</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>

<!-- Prestige Banner -->
<section class="prestige-banner">
    <div class="prestige-container">
        <div class="prestige-item">
            <h3 data-target="150">0</h3>
            <p>Years of Excellence</p>
        </div>
        <div class="prestige-item">
            <h3 data-target="98.7">0</h3>
            <p>Success Rate %</p>
        </div>
        <div class="prestige-item">
            <h3 data-target="1000">0</h3>
            <p>Industry Leaders</p>
        </div>
        <div class="prestige-item">
            <h3 data-target="∞">0</h3>
            <p>Possibilities</p>
        </div>
    </div>
</section>

<!-- Features Showcase -->
<section class="features-section" id="features">
    <div class="feature-showcase scroll-animate">
        <div class="feature-content">
            <h2>Quantum Class Management</h2>
            <p>Administer infinite realities of class configurations. Select, assign, and manage student cohorts across multiversal timetables with batch operations that transcend linear time.</p>
            <div class="cta-button" style="font-size: 0.9rem; padding: 0.75rem 1.5rem;">
                Explore OS
            </div>
        </div>
        <div class="feature-image animated-border">
            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&h=400&fit=crop" alt="Quantum management">
        </div>
    </div>

    <div class="feature-showcase scroll-animate">
        <div class="feature-content">
            <h2>Sentient Attendance Analytics</h2>
            <p>Our AI doesn't just track attendance—it predicts academic futures. Toggle between presence rates and absence statistics with neural-network precision. Automatic flagging at 25% threshold with quantum probability analysis.</p>
            <div class="cta-button" style="font-size: 0.9rem; padding: 0.75rem 1.5rem;">
                View Predictions
            </div>
        </div>
        <div class="feature-image animated-border">
            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=400&fit=crop" alt="AI analytics">
        </div>
    </div>

    <div class="feature-showcase scroll-animate">
        <div class="feature-content">
            <h2>Singularity-Grade Modules</h2>
            <p>Create course modules that exist in permanent quantum superposition—simultaneously available across all timelines. Instant cataloguing in the academic singularity upon creation.</p>
            <div class="cta-button" style="font-size: 0.9rem; padding: 0.75rem 1.5rem;">
                Build Singularity
            </div>
        </div>
        <div class="feature-image animated-border">
            <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=800&h=400&fit=crop" alt="Module creation">
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section" id="testimonials">
    <div class="testimonials-container">
        <div class="section-header scroll-animate">
            <h2 style="font-family: 'Playfair Display', serif; font-size: 3rem; margin-bottom: 1rem;">Consciousness That Evolved Here</h2>
            <p style="color: var(--text-muted); font-size: 1.1rem;">Testimonies from across the spacetime continuum</p>
        </div>
        
        <div class="testimonial-carousel scroll-animate">
            <div class="testimonial-slide">
                <p class="testimonial-quote">"The academic OS didn't just manage my education—it predicted my Nobel-worthy breakthroughs three years in advance."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="author-info">
                        <h4>Dr. Synth-7</h4>
                        <p>Quantum Consciousness Lab, 2047</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Experience Video -->
<section class="video-section" id="video">
    <div class="video-container scroll-animate">
        <div class="video-placeholder" id="videoTrigger">
            <img src="https://images.unsplash.com/photo-1560331130-7a48c2b5040a?w=1000&h=563&fit=crop" alt="Singularity experience">
            <div class="play-button">
                <i class="bi bi-play-fill"></i>
            </div>
        </div>
    </div>
</section>

<!-- Centered CTA -->
<section class="cta-section">
    <div class="cta-content scroll-animate">
        <h2>Your Legacy Awaits</h2>
        <p>Initialize your access to the singularity. Join the consciousness that shapes realities.</p>
        <a href="requestacc.php" class="cta-button">
            <span>Request Singularity Access</span>
            <i class="bi bi-key-fill"></i>
        </a>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <h3>macademia Faculty OS</h3>
        <p>&copy; 2024 Quantum Academic Systems. All rights reserved across all timelines.</p>
        <div class="footer-links">
            <a href="#">Privacy Protocols</a>
            <a href="#">Terms of Singularity</a>
            <a href="#">Contact Nexus</a>
        </div>
    </div>
</footer>

<!-- VIDEO MODAL -->
<div class="video-modal" id="videoModal">
    <div class="modal-content">
        <span class="close-modal" id="closeModal">&times;</span>
        <video id="modalVideo" controls>
            <source src="campus-tour.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <p style="color: var(--text-muted); padding: 1rem; text-align: center;">
            Imagine the most epic campus tour possible. This is your university's story.
        </p>
    </div>
</div>

<style>
/* Video Modal */
.video-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 14, 26, 0.95);
    backdrop-filter: var(--glass-blur);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.video-modal.show {
    display: flex;
}

.modal-content {
    max-width: 90vw;
    max-height: 90vh;
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    overflow: hidden;
    position: relative;
}

.close-modal {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2rem;
    color: var(--primary);
    cursor: pointer;
    z-index: 10001;
}

video {
    width: 100%;
    max-width: 800px;
    display: block;
}
</style>

<script>
// CUSTOM CURSOR
const cursor = document.querySelector('.cursor');
const follower = document.querySelector('.cursor-follower');
let mouseX = 0, mouseY = 0, followerX = 0, followerY = 0;

document.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;
});

function animateCursor() {
    followerX += (mouseX - followerX) * 0.1;
    followerY += (mouseY - followerY) * 0.1;
    
    cursor.style.left = mouseX - 10 + 'px';
    cursor.style.top = mouseY - 10 + 'px';
    
    follower.style.left = followerX - 20 + 'px';
    follower.style.top = followerY - 20 + 'px';
    
    requestAnimationFrame(animateCursor);
}
animateCursor();

// MATRIX RAIN
const canvas = document.querySelector('.matrix-bg');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

const matrixChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789@#$%^&*()*&^%+-/~{[|`]}";
const matrixArray = matrixChars.split("");

const fontSize = 10;
const columns = canvas.width / fontSize;

const drops = [];
for(let x = 0; x < columns; x++) {
    drops[x] = 1;
}

function drawMatrix() {
    ctx.fillStyle = 'rgba(10, 14, 26, 0.04)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    ctx.fillStyle = '#00f5ff';
    ctx.font = fontSize + 'px monospace';
    
    for(let i = 0; i < drops.length; i++) {
        const text = matrixArray[Math.floor(Math.random() * matrixArray.length)];
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);
        
        if(drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
            drops[i] = 0;
        }
        drops[i]++;
    }
}

setInterval(drawMatrix, 35);

// TYPING EFFECT
function typeWriter(text, element, speed = 50) {
    let i = 0;
    element.innerHTML = '';
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        } else {
            element.classList.remove('typing-cursor');
        }
    }
    type();
}

// Initialize typing effect after page load
window.addEventListener('load', () => {
    const subtitle = document.getElementById('heroSubtitle');
    const originalText = subtitle.textContent;
    typeWriter(originalText, subtitle, 40);
});

// PARALLAX HERO
const heroBg = document.querySelector('.hero-bg');
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    heroBg.style.transform = `translateY(${scrolled * 0.5}px)`;
});

// SCROLL ANIMATIONS
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

document.querySelectorAll('.scroll-animate').forEach(el => {
    observer.observe(el);
});

// PRESTIGE COUNTER
function animateCounters() {
    const counters = document.querySelectorAll('.prestige-item h3');
    counters.forEach(counter => {
        const target = counter.getAttribute('data-target');
        const duration = 2000;
        const start = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            
            if(target === '∞') {
                counter.textContent = '∞';
            } else {
                const value = parseFloat(target);
                const current = value * progress;
                if(value % 1 === 0) {
                    counter.textContent = Math.floor(current);
                } else {
                    counter.textContent = current.toFixed(1);
                }
            }
            
            if(progress < 1) {
                requestAnimationFrame(update);
            }
        }
        requestAnimationFrame(update);
    });
}

const prestigeSection = document.querySelector('.prestige-banner');
const prestigeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            prestigeObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

prestigeObserver.observe(prestigeSection);

// PARTICLE EXPLOSION
function createExplosion(x, y) {
    const particleCount = 30;
    const explosion = document.createElement('div');
    explosion.className = 'particles-explosion';
    explosion.style.left = x + 'px';
    explosion.style.top = y + 'px';
    document.body.appendChild(explosion);
    
    for(let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle-explode';
        const angle = (Math.PI * 2 * i) / particleCount;
        const velocity = 50 + Math.random() * 50;
        particle.style.setProperty('--x', Math.cos(angle) * velocity + 'px');
        particle.style.setProperty('--y', Math.sin(angle) * velocity + 'px');
        particle.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
        explosion.appendChild(particle);
        
        setTimeout(() => particle.remove(), 1000);
    }
    
    setTimeout(() => explosion.remove(), 1000);
}

// Add explosion to CTA clicks
document.querySelectorAll('.cta-button').forEach(button => {
    button.addEventListener('click', (e) => {
        createExplosion(e.clientX, e.clientY);
    });
});

// VIDEO MODAL
const videoModal = document.getElementById('videoModal');
const videoTrigger = document.getElementById('videoTrigger');
const closeModal = document.getElementById('closeModal');
const modalVideo = document.getElementById('modalVideo');

videoTrigger.addEventListener('click', () => {
    videoModal.classList.add('show');
    modalVideo.play().catch(e => console.log("Video play failed:", e));
});

closeModal.addEventListener('click', () => {
    videoModal.classList.remove('show');
    modalVideo.pause();
    modalVideo.currentTime = 0;
});

videoModal.addEventListener('click', (e) => {
    if(e.target === videoModal) {
        videoModal.classList.remove('show');
        modalVideo.pause();
        modalVideo.currentTime = 0;
    }
});

// KONAMI CODE EASTER EGG
let konamiCode = [];
const secretCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', (e) => {
    konamiCode.push(e.key);
    konamiCode = konamiCode.slice(-10);
    
    if(konamiCode.join(',') === secretCode.join(',')) {
        document.body.style.filter = 'hue-rotate(180deg) saturate(2)';
        alert('🎮 GOD MODE ACTIVATED 🎮\n\nAll systems unlocked. Welcome, developer.');
    }
});

// SMOOTH SCROLL
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
</script>
</body>
</html>