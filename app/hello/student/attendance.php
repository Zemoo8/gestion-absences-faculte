<?php
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}
global $mysqli;
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: " . BASE_URL . "/login/login");
    exit();
}
$student_id = $_SESSION['user_id'];
$student_info = $mysqli->query("SELECT photo_path, nom, prenom FROM users WHERE id = $student_id")->fetch_assoc();
$stats_result = $mysqli->query("SELECT 
    (SELECT COUNT(DISTINCT a.module_id) FROM attendance a WHERE a.student_id = $student_id) as total_modules,
    (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id AND status = 'absent') as total_absences,
    (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id) as total_classes
");
if ($stats_result && ($row = $stats_result->fetch_assoc())) {
    $stats = $row;
} else {
    $stats = [
        'total_modules' => 0,
        'total_absences' => 0,
        'total_classes' => 0
    ];
}
$attendance_rate = $stats['total_classes'] > 0 
    ? round((($stats['total_classes'] - $stats['total_absences']) / $stats['total_classes']) * 100, 1) 
    : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Attendance Summary | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* Dark Theme (Default) */
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

/* Light Theme */
:root[data-theme="light"] {
    --primary: #8B5E3C;
    --primary-glow: rgba(139,94,60,0.12);
    --secondary: #5A8C6F;
    --accent: #B8956A;
    --bg-main: linear-gradient(180deg, #f4efe6 0%, #efe7d9 100%);
    --bg-panel: rgba(255, 255, 255, 0.9);
    --bg-card: #ffffff;
    --bg-card-border: rgba(0, 0, 0, 0.06);
    --text-primary: #3a3a3a;
    --text-secondary: #5a5a5a;
    --text-muted: #7a7a7a;
    --error: #c53030;
    --success: #3e9f5f;
    --shadow: 0 12px 30px rgba(15,15,15,0.08);
    --transition: all 0.3s ease;
    --glass-blur: blur(8px) saturate(120%);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-main);
    background-size: 400% 400%;
    animation: gradientShift 25s ease infinite;
    color: var(--text-primary);
    overflow-x: hidden;
    min-height: 100vh;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.theme-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--bg-card-border);
    color: var(--text-secondary);
    padding: 0.5rem 1rem;
    border-radius: 12px;
    cursor: pointer;
    font-size: 1rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-main);
    background-size: 400% 400%;
    animation: gradientShift 25s ease infinite;
    color: var(--text-primary);
    overflow-x: hidden;
    min-height: 100vh;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 70px;
    background: var(--bg-panel);
    backdrop-filter: var(--glass-blur);
    border-bottom: 1px solid var(--bg-card-border);
    display: flex;
    align-items: center;
    padding: 0 2rem;
    justify-content: space-between;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: var(--transition);
}

.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.05); }

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--text-primary);
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 20px var(--primary-glow); }
    50% { transform: scale(1.08); box-shadow: 0 0 40px var(--primary-glow); }
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.navbar-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-secondary);
    font-weight: 600;
}

.user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--primary);
    display: grid;
    place-items: center;
    font-size: 1rem;
    font-weight: 700;
}

.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
    margin-top: 70px;
}

.sidebar {
    width: 280px;
    background: var(--bg-panel);
    border-right: 1px solid var(--bg-card-border);
    padding: 1.5rem 0;
    position: fixed;
    left: 0;
    top: 70px;
    height: calc(100vh - 70px);
    overflow-y: auto;
    transition: var(--transition);
    z-index: 999;
}

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar-menu {
    list-style: none;
}

.sidebar-item {
    margin-bottom: 0.25rem;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    border-radius: 0 12px 12px 0;
    transition: var(--transition);
}

.sidebar-link.active {
    color: var(--primary);
    background: rgba(255, 255, 255, 0.04);
}

:root[data-theme="light"] .sidebar-link.active {
    background: rgba(139, 94, 60, 0.08);
}

.sidebar-link:hover {
    color: var(--primary);
    background: rgba(255, 255, 255, 0.04);
}

:root[data-theme="light"] .sidebar-link:hover {
    background: rgba(139, 94, 60, 0.08);
}

.sidebar-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    min-height: 80vh;
    width: 100%;
}

.sidebar.collapsed ~ .main-content { margin-left: 0; }

.info-card {
    width:100%;
    max-width:600px;
    margin-top:3rem;
    box-shadow:var(--shadow);
    background:var(--bg-card);
    backdrop-filter:var(--glass-blur);
    border:1px solid var(--bg-card-border);
    border-radius:20px;
    padding:2.5rem 2.5rem;
    display:flex;
    flex-direction:column;
    align-items:center;
}

@media (max-width: 768px) {
    .navbar { padding: 0 1rem; }
    .sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        height: auto;
        width: 100%;
        border-top: 1px solid var(--bg-card-border);
        border-right: none;
        padding: 0.5rem 0;
    }
    .sidebar.collapsed { transform: translateY(100%); }
    .sidebar-menu {
        display: flex;
        overflow-x: auto;
        padding: 0 1rem;
        justify-content: space-around;
    }
    .sidebar-link {
        flex-direction: column;
        padding: 0.5rem;
        font-size: 0.7rem;
        min-width: 70px;
    }
    .sidebar-link span { display: none; }
    .main-content {
        margin-left: 0;
        padding: 1rem;
        padding-bottom: 100px;
        flex-direction: column;
    }
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <button class="theme-toggle" id="themeToggle" title="Toggle theme">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        <div class="user-menu">
            <span><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
            <?php
            $photo = isset($student_info['photo_path']) ? $student_info['photo_path'] : null;
            if ($photo && file_exists(__DIR__ . '/../../../public/' . $photo)):
                $public_url = defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost';
            ?>
                <img src="<?php echo $public_url . '/' . htmlspecialchars($photo); ?>" alt="Profile" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; object-position: center;">
            <?php else: ?>
                <div class="user-avatar"><?php echo substr($_SESSION['prenom'], 0, 1); ?></div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="dashboard-wrapper">
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="/projet/Gestion-absences/public/index.php/studdash/profile" class="sidebar-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
            <li><a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link active"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
            <li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="info-card">
            <h3 style="font-size:2rem;font-weight:800;color:var(--primary);margin-bottom:2rem;">Attendance Summary</h3>
            <div style="display:flex;flex-direction:column;align-items:center;gap:2rem;">
                <div style="position:relative;width:180px;height:180px;">
                    <svg width="180" height="180">
                        <circle cx="90" cy="90" r="80" stroke="#222b45" stroke-width="16" fill="none"/>
                        <circle cx="90" cy="90" r="80" stroke="var(--primary)" stroke-width="16" fill="none" stroke-dasharray="502" stroke-dashoffset="<?php echo 502 - round($attendance_rate/100*502); ?>" style="transition:stroke-dashoffset 1s;"/>
                    </svg>
                    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;font-weight:900;color:var(--primary);">
                        <?php echo $attendance_rate; ?>%
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;width:100%;max-width:350px;">
                    <div style="text-align:center;">
                        <div style="font-size:1.1rem;color:var(--text-secondary);">Total Classes </div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--text-primary);margin-top:0.5rem;">
                            <?php echo $stats['total_classes']; ?>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.1rem;color:var(--text-secondary);padding-left:6px;">Absences</div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--error);margin-top:0.5rem;">
                            <?php echo $stats['total_absences']; ?>
                        </div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.1rem;color:var(--text-secondary);padding-left:6px;">Enrolled Modules</div>
                        <div style="font-size:1.5rem;font-weight:700;color:var(--primary);margin-top:0.5rem;">
                            <?php echo $stats['total_modules']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// Theme Toggle Functionality
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const root = document.documentElement;

// Get saved theme or default to 'dark'
const savedTheme = localStorage.getItem('theme') || 'dark';

// Apply the saved theme on page load
if (savedTheme === 'light') {
    root.setAttribute('data-theme', 'light');
    themeIcon.classList.remove('bi-moon-fill');
    themeIcon.classList.add('bi-sun-fill');
}

// Toggle theme on button click
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