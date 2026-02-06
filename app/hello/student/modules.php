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
$my_modules = $mysqli->query("
    SELECT m.module_name, u.prenom as prof_prenom, u.nom as prof_nom, ms.weekday, ms.start_time
    FROM modules m
    JOIN users u ON m.professor_id = u.id
    LEFT JOIN module_schedule ms ON m.id = ms.module_id
    WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
");
$module_count = $my_modules ? $my_modules->num_rows : 0;
$days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Modules | macademia Faculty</title>
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
    --error: #A67B6E;
    --success: #7A9E7D;
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

.hero-header {
    width:100%;
    max-width:900px;
    margin:3rem auto 2rem auto;
    padding:2.5rem 2rem;
    background:var(--bg-card);
    border-radius:24px;
    box-shadow:0 8px 32px rgba(0,0,0,0.18);
    display:flex;
    align-items:center;
    gap:2rem;
}

.hero-icon {
    width:80px;
    height:80px;
    background:var(--primary);
    border-radius:50%;
    display:grid;
    place-items:center;
    font-size:2.5rem;
    box-shadow:0 0 30px var(--primary-glow);
}

.hero-content h1 {
    font-size:2.3rem;
    font-weight:900;
    margin:0;
    color:var(--primary);
    letter-spacing:-1px;
}

.hero-content p {
    color:var(--text-secondary);
    font-size:1.15rem;
    margin-top:0.5rem;
}

.modules-grid {
    width:100%;
    max-width:900px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:2rem;
    margin-top:2rem;
}

.module-card {
    background:var(--bg-card);
    border-radius:18px;
    padding:2rem 1.5rem;
    box-shadow:0 2px 12px rgba(0,0,0,0.10);
    display:flex;
    flex-direction:column;
    gap:1rem;
    align-items:flex-start;
    transition:transform 0.2s;
}

.module-card:hover {
    transform:translateY(-6px) scale(1.03);
    box-shadow:0 8px 32px var(--primary-glow);
}

.module-title {
    font-size:1.35rem;
    font-weight:800;
    color:var(--primary);
    margin-bottom:0.2rem;
}

.module-prof {
    font-size:1.05rem;
    color:var(--text-secondary);
}

.module-sched {
    font-size:0.98rem;
    color:var(--text-muted);
    margin-top:0.2rem;
}

.module-badge {
    display:inline-block;
    background:var(--primary);
    color:white;
    font-size:0.85rem;
    font-weight:700;
    padding:0.3rem 0.9rem;
    border-radius:12px;
    margin-bottom:0.7rem;
    letter-spacing:0.5px;
    box-shadow:0 2px 8px var(--primary-glow);
}

.modules-stats {
    width:100%;
    max-width:900px;
    display:flex;
    gap:2rem;
    justify-content:space-between;
    margin:2.5rem auto 0 auto;
}

.stat-card {
    flex:1;
    background:rgba(255,255,255,0.04);
    border-radius:18px;
    padding:1.5rem 1.2rem;
    box-shadow:0 2px 12px rgba(0,0,0,0.10);
    display:flex;
    flex-direction:column;
    align-items:center;
}

.stat-label {
    color:var(--text-secondary);
    font-size:1.05rem;
    margin-bottom:0.5rem;
}

.stat-value {
    font-size:2.1rem;
    font-weight:900;
    color:var(--primary);
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
    .hero-header {
        flex-direction: column;
        text-align: center;
        padding: 1.5rem;
    }
    .modules-stats {
        flex-direction: column;
        gap: 1rem;
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
            <li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link active"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
            <li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="hero-header">
            <div class="hero-icon"><i class="bi bi-bookshelf"></i></div>
            <div class="hero-content">
                <h1>My Modules</h1>
                <p>Explore your enrolled modules, meet your professors, and see your weekly schedule at a glance. Stay on top of your academic journey!</p>
            </div>
        </div>
        <div class="modules-stats">
            <div class="stat-card">
                <div class="stat-label">Total Modules</div>
                <div class="stat-value"><?php echo $module_count; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Unique Professors</div>
                <div class="stat-value">
                <?php
                $profs = $mysqli->query("SELECT COUNT(DISTINCT professor_id) as profs FROM modules WHERE id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)");
                echo $profs && ($row = $profs->fetch_assoc()) ? $row['profs'] : 0;
                ?>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Earliest Class</div>
                <div class="stat-value">
                <?php
                $earliest = $mysqli->query("SELECT MIN(start_time) as min_time FROM module_schedule WHERE module_id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)");
                echo $earliest && ($row = $earliest->fetch_assoc()) && $row['min_time'] ? substr($row['min_time'],0,5) : '--:--';
                ?>
                </div>
            </div>
        </div>
        <div class="modules-grid">
            <?php if($my_modules && $my_modules->num_rows > 0): ?>
                <?php while($mod = $my_modules->fetch_assoc()): ?>
                <div class="module-card">
                    <span class="module-badge"><i class="bi bi-mortarboard"></i> Module</span>
                    <div class="module-title"><?php echo htmlspecialchars($mod['module_name']); ?></div>
                    <div class="module-prof">Professor: <?php echo htmlspecialchars($mod['prof_prenom'] . ' ' . $mod['prof_nom']); ?></div>
                    <?php if(!empty($mod['weekday']) && !empty($mod['start_time'])): ?>
                        <div class="module-sched"><i class="bi bi-calendar-event"></i> <?php echo $days[(int)$mod['weekday']] ?? $mod['weekday']; ?>, <?php echo $mod['start_time']; ?></div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="module-card" style="opacity:0.7;text-align:center;">
                    <span class="module-badge"><i class="bi bi-mortarboard"></i> Module</span>
                    <div class="module-title">No modules enrolled</div>
                    <div class="module-prof">Check with your faculty for enrollment details.</div>
                </div>
            <?php endif; ?>
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