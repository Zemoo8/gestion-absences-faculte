<?php
// Bootstrap loads config and starts session; view remains presentation-only.

if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

$prof_id = $_SESSION['user_id'];

// Get module list for filter
$modules = $mysqli->query("SELECT id, module_name FROM modules WHERE professor_id = $prof_id ORDER BY module_name");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Reports | macademia Faculty</title>
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
    color: var(--text-primary);
    overflow-x: hidden;
}

/* === NAVBAR === */
.navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
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

/* Notification Dropdown */
.notification-wrapper {
    position: relative;
}

.notification-bell {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: var(--transition);
    position: relative;
}
.notification-bell:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--primary);
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 1rem;
    background: #0a0e27;
    border: 1px solid var(--bg-card-border);
    border-radius: 14px;
    min-width: 320px;
    box-shadow: var(--shadow);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition);
    z-index: 1001;
}
.notification-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: var(--transition);
}
.notification-item:hover {
    background: rgba(255, 255, 255, 0.03);
}

.dropdown-empty {
    padding: 1rem;
    text-align: center;
    color: var(--text-muted);
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
    background: linear-gradient(135deg, var(--secondary), var(--accent));
    display: grid;
    place-items: center;
    font-size: 1rem;
    font-weight: 700;
}

/* === MAIN LAYOUT === */
.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
    margin-top: 70px;
}

/* === SIDEBAR === */
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

.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.02);
    color: var(--text-primary);
}

.sidebar-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

/* === MAIN CONTENT === */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    transition: var(--transition);
}

.sidebar.collapsed ~ .main-content {
    margin-left: 0;
}

/* === EXISTING PAGE STYLES === */
:root {
    --primary: #00f5ff;
    --accent: #f72b7b;
    --bg-main: linear-gradient(135deg, #0a0e27 0%, #12172f 50%);
    --bg-card: rgba(255, 255, 255, 0.04);
    --bg-card-border: rgba(255, 255, 255, 0.08);
    --text-primary: #f0f4f8;
}
body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    color: var(--text-primary);
    padding: 2rem;
}
.report-card {
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
}
.download-btn {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin-top: 1rem;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .navbar { padding: 0 1rem; }
    .sidebar {
        position: fixed; bottom: 0; left: 0; right: 0; top: auto;
        height: auto; width: 100%; border-top: 1px solid var(--bg-card-border);
        border-right: none; padding: 0.5rem 0; transform: translateY(100%);
    }
    .sidebar.collapsed { transform: translateY(100%); }
    .sidebar:not(.collapsed) { transform: translateY(0); }
    .sidebar-menu {
        display: flex; overflow-x: auto; padding: 0 1rem; justify-content: space-around;
    }
    .sidebar-link { flex-direction: column; padding: 0.5rem; font-size: 0.7rem; min-width: 70px; }
    .sidebar-link span { display: none; }
    .main-content { margin-left: 0; padding: 1rem; padding-bottom: 100px; }
}
</style>
</head>
<body>

<!-- === NAVBAR === -->
<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a href="<?= PUBLIC_URL ?>/index.php/profdash" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <div class="notification-wrapper">
            <button class="notification-bell" id="notificationBell">
                <i class="bi bi-bell-fill"></i>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <p class="dropdown-empty">No new notifications</p>
            </div>
        </div>
        
        <div class="user-menu">
            <span>Prof. <?= htmlspecialchars($_SESSION['user_name'] ?? 'Professor') ?></span>
            <div class="user-avatar"><?= substr($_SESSION['user_name'] ?? 'P', 0, 1) ?></div>
        </div>
    </div>
</nav>

<!-- === MAIN WRAPPER === -->
<div class="dashboard-wrapper">
    <!-- === SIDEBAR === -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?= PUBLIC_URL ?>/index.php/profdash" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="<?= PUBLIC_URL ?>/index.php/profdash/my_modules" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="<?= PUBLIC_URL ?>/index.php/profdash/students" class="sidebar-link"><i class="bi bi-people"></i><span>Students</span></a></li>
            <li><a href="<?= PUBLIC_URL ?>/index.php/profdash/reports" class="sidebar-link active"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
            <li><a href="<?= PUBLIC_URL ?>/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main class="main-content">
        <h1 style="margin-bottom: 2rem;">Attendance Reports</h1>

        <div class="report-card">
            <h2 style="margin-bottom: 1rem;">Export Module Report</h2>
            <p style="color: #94a3b8; margin-bottom: 1rem;">Generate PDF report for selected module</p>
            
            <select id="moduleSelect" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--bg-card-border); color: var(--text-primary);">
                <option value="">Select Module</option>
                <?php while ($mod = $modules->fetch_assoc()): ?>
                <option value="<?php echo $mod['id']; ?>"><?php echo htmlspecialchars($mod['module_name']); ?></option>
                <?php endwhile; ?>
            </select>
            
            <button class="download-btn" onclick="alert('PDF generation coming soon');">
                <i class="bi bi-download"></i> Download PDF
            </button>
        </div>

        <div class="report-card">
            <h2 style="margin-bottom: 1rem;">At-Risk Students Report</h2>
            <p style="color: #94a3b8; margin-bottom: 1rem;">List students with >20% absence rate</p>
            <button class="download-btn" onclick="location.href='students.php'">
                <i class="bi bi-exclamation-triangle"></i> View At-Risk Students
            </button>
        </div>
    </main>
</div>

<script>
// === SIDEBAR TOGGLE ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
sidebarToggle.addEventListener('click', () => { sidebar.classList.toggle('collapsed'); });

// === NOTIFICATION DROPDOWN ===
const notificationBell = document.getElementById('notificationBell');
const notificationDropdown = document.getElementById('notificationDropdown');
notificationBell.addEventListener('click', (e) => {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
});
document.addEventListener('click', (e) => {
    if (!notificationBell.contains(e.target)) {
        notificationDropdown.classList.remove('show');
    }
});
</script>

</body>
</html>



