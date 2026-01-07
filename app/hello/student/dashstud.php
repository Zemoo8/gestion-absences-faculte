<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// If this view is opened directly, redirect to the front-controller student route
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
    $base = defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    header('Location: ' . $base . '/index.php/studdash/dashstud');
    exit();
}

// Make DB connection available
global $mysqli;

// Access control: only students
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: " . BASE_URL . "/login/login");
    exit();
}

$student_id = $_SESSION['user_id'];

// Student stats
$stats = $mysqli->query("
    SELECT 
        (SELECT COUNT(DISTINCT a.module_id) FROM attendance a WHERE a.student_id = $student_id) as total_modules,
        (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id AND status = 'absent') as total_absences,
        (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id) as total_classes,
        (SELECT COUNT(*) FROM modules m 
         INNER JOIN attendance a ON m.id = a.module_id 
         WHERE a.student_id = $student_id 
         AND DAYOFWEEK(CURDATE()) = DAYOFWEEK(a.date)) as today_classes
")->fetch_assoc();

// Calculate attendance rate
$attendance_rate = $stats['total_classes'] > 0 
    ? round((($stats['total_classes'] - $stats['total_absences']) / $stats['total_classes']) * 100, 1) 
    : 0;

// My Modules
$my_modules = $mysqli->query("
    SELECT m.module_name, u.prenom as prof_prenom, u.nom as prof_nom, ms.day as schedule_day, ms.start_time as schedule_time
    FROM modules m
    JOIN users u ON m.professor_id = u.id
    LEFT JOIN module_schedule ms ON m.id = ms.module_id
    WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
");

// Schedule
$schedule = $mysqli->query("
    SELECT m.module_name, ms.day as schedule_day, ms.start_time as schedule_time
    FROM modules m
    JOIN module_schedule ms ON m.id = ms.module_id
    WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
    ORDER BY ms.day, ms.start_time
");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | macademia Faculty</title>
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
    --warning: #ffa500;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    --glass-blur: blur(24px) saturate(200%);
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
    background: linear-gradient(135deg, var(--secondary), var(--accent));
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

.sidebar.collapsed { transform: translateX(-100%); }

.sidebar-menu { list-style: none; }

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
    gap: 2rem;
}

.sidebar.collapsed ~ .main-content { margin-left: 0; }

.chatbot-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.chatbot-placeholder {
    width: 100%;
    max-width: 800px;
    height: 500px;
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    box-shadow: var(--shadow);
    position: relative;
    overflow: hidden;
}

.chatbot-placeholder::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--accent));
}

.chatbot-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 3rem;
    animation: pulse 2s infinite;
    box-shadow: 0 0 30px var(--primary-glow);
}

.chatbot-placeholder h2 {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.chatbot-placeholder p {
    color: var(--text-muted);
    font-size: 1.1rem;
    text-align: center;
    padding: 0 2rem;
}

.info-panel {
    width: 350px;
    flex-shrink: 0;
}

.info-card {
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow);
}

.info-card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--primary);
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--bg-card-border);
}

.stat-row:last-child { border-bottom: none; }

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.stat-value {
    font-weight: 700;
    color: var(--text-primary);
}

.stat-value.high { color: var(--success); }
.stat-value.medium { color: var(--warning); }
.stat-value.low { color: var(--error); }

.module-item {
    padding: 0.75rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    margin-bottom: 0.75rem;
    border: 1px solid transparent;
    transition: var(--transition);
}

.module-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--bg-card-border);
}

.module-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.module-details {
    font-size: 0.85rem;
    color: var(--text-muted);
}

.schedule-item {
    padding: 0.75rem;
    border-left: 3px solid var(--primary);
    margin-bottom: 0.75rem;
    background: rgba(0, 245, 255, 0.05);
    border-radius: 0 8px 8px 0;
}

.schedule-time {
    font-weight: 600;
    color: var(--primary);
    font-size: 0.9rem;
}

.schedule-name {
    color: var(--text-secondary);
    font-size: 0.85rem;
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
    .info-panel { width: 100%; order: -1; }
    .chatbot-placeholder { height: 400px; }
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/studdash/dashstud" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <div class="user-menu">
            <span><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
            <div class="user-avatar"><?php echo substr($_SESSION['prenom'], 0, 1); ?></div>
        </div>
    </div>
</nav>

<div class="dashboard-wrapper">
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/studdash/dashstud" class="sidebar-link active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')); ?>/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="chatbot-container">
            <div class="chatbot-placeholder">
                <div class="chatbot-icon">
                    <i class="bi bi-robot"></i>
                </div>
                <h2>Welcome to Your Dashboard</h2>
                <p>View your attendance, modules, and schedule information.</p>
            </div>
        </div>

        <div class="info-panel">
            <div class="info-card">
                <h3>Attendance Summary</h3>
                <div class="stat-row">
                    <span class="stat-label">Attendance Rate</span>
                    <span class="stat-value <?php echo $attendance_rate >= 75 ? 'high' : ($attendance_rate >= 50 ? 'medium' : 'low'); ?>">
                        <?php echo $attendance_rate; ?>%
                    </span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Total Classes</span>
                    <span class="stat-value"><?php echo $stats['total_classes']; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Absences</span>
                    <span class="stat-value low"><?php echo $stats['total_absences']; ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Enrolled Modules</span>
                    <span class="stat-value"><?php echo $stats['total_modules']; ?></span>
                </div>
            </div>

            <div class="info-card">
                <h3>My Modules</h3>
                <?php if($my_modules && $my_modules->num_rows > 0): ?>
                    <?php while($mod = $my_modules->fetch_assoc()): ?>
                    <div class="module-item">
                        <div class="module-name"><?php echo htmlspecialchars($mod['module_name']); ?></div>
                        <div class="module-details">
                            <?php echo htmlspecialchars($mod['prof_prenom'] . ' ' . $mod['prof_nom']); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No modules enrolled</p>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h3>This Week's Schedule</h3>
                <?php if($schedule && $schedule->num_rows > 0): ?>
                    <?php while($sch = $schedule->fetch_assoc()): ?>
                    <div class="schedule-item">
                        <div class="schedule-time"><?php echo $sch['schedule_day'] . ' ' . $sch['schedule_time']; ?></div>
                        <div class="schedule-name"><?php echo htmlspecialchars($sch['module_name']); ?></div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>  
                    <p style="color: var(--text-muted); font-size: 0.9rem;">No classes scheduled this week</p>
                <?php endif; ?>
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
</script>

</body>
</html>