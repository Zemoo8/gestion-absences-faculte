<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// expose DB connection to this view
global $mysqli;

// Bootstrap loads config and starts session; view remains presentation-only.

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

// Stats
$stats = $mysqli->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM modules) as total_modules,
        (SELECT COUNT(*) FROM attendance WHERE date = CURDATE()) as today_attendance,
        (SELECT COUNT(*) FROM account_requests WHERE status = 'pending') as pending_requests
")->fetch_assoc();

// Recent attendance
$recent_attendance = $mysqli->query("
    SELECT a.id, u.nom, u.prenom, m.module_name, a.date, a.status
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    JOIN modules m ON a.module_id = m.id
    ORDER BY a.date DESC, a.id DESC
    LIMIT 10
");

// Modules with today's count
$modules = $mysqli->query("
    SELECT m.id, m.module_name, u.nom as prof_nom, u.prenom as prof_prenom, 
           (SELECT COUNT(*) FROM attendance WHERE module_id = m.id AND date = CURDATE() AND status='present') as today_count
    FROM modules m
    LEFT JOIN users u ON m.professor_id = u.id
    ORDER BY m.module_name
    LIMIT 8
");

// Pending requests
$pending_requests = $mysqli->query("
    SELECT id, nom, prenom, email, created_at 
    FROM account_requests 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | macademia Faculty</title>
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
    --secondary: #3B6A47;
    --accent: #A67C52;
    --bg-main: linear-gradient(180deg, #f4efe6 0%, #efe7d9 100%);
    --bg-panel: rgba(255, 255, 255, 0.9);
    --bg-card: #ffffff;
    --bg-card-border: rgba(0, 0, 0, 0.06);
    --text-primary: #2b2b2b;
    --text-secondary: #4b4b4b;
    --text-muted: #6b6b6b;
    --error: #c53030;
    --success: #2f855a;
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
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* === FIXED NAVBAR === */
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

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.05);
}

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

.notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: var(--error);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    animation: pulseBadge 2s ease infinite;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 1rem;
    backdrop-filter: var(--glass-blur);
    background: var(--bg-panel);
    border: 1px solid var(--bg-card-border);
    border-radius: 14px;
    min-width: 320px;
    max-width: 400px;
    box-shadow: var(--shadow);
    opacity: 1;
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

.btn-small {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
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

.notification-content strong {
    color: var(--primary);
    font-size: 0.875rem;
}

.notification-content p {
    margin: 0.25rem 0;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.notification-content small {
    color: var(--text-muted);
    font-size: 0.75rem;
}

.btn-approve {
    background: rgba(0, 230, 118, 0.1);
    color: var(--success);
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(0, 230, 118, 0.2);
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

/* === MAIN CONTENT === */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    transition: var(--transition);
    display: flex;
    gap: 2rem;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 0;
}

/* === CALENDAR WIDGET (SMALLER, RIGHT SIDE) === */
.calendar-container {
    width: 300px;
    flex-shrink: 0;
}

.calendar-widget {
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 1.25rem;
    box-shadow: var(--shadow);
    z-index: 998;
    position: sticky;
    top: 90px;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.calendar-header h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.calendar-header button {
    background: none;
    border: none;
    color: var(--primary);
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1rem;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
}

.calendar-day {
    aspect-ratio: 1;
    display: grid;
    place-items: center;
    border-radius: 8px;
    font-size: 0.75rem;
    color: var(--text-secondary);
    cursor: pointer;
    transition: var(--transition);
}

.calendar-day:hover {
    background: rgba(255, 255, 255, 0.05);
}

.calendar-day.today {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    font-weight: 700;
}

.calendar-day.has-event {
    background: rgba(0, 245, 255, 0.1);
    color: var(--primary);
}

/* === MAIN CONTENT AREA === */
.content-area {
    flex: 1;
}

/* === STATS CARDS === */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 1.75rem;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    border-color: rgba(0, 245, 255, 0.3);
}

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-label {
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 500;
}

.stat-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, rgba(0, 245, 255, 0.1), rgba(123, 47, 247, 0.1));
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: var(--primary);
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin: 0.5rem 0;
}

/* === CONTENT CARDS === */
.content-card {
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 2rem;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.content-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px var(--primary-glow);
}

/* === MODULE GRID === */
.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.module-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    border: 1px solid transparent;
    transition: var(--transition);
}

.module-item:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--bg-card-border);
}

.module-info h4 {
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.module-info p {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.module-stats {
    text-align: center;
}

.attendance-count {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.module-stats small {
    color: var(--text-muted);
    font-size: 0.75rem;
}

/* === TABLES === */
.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background: rgba(255, 255, 255, 0.06);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--primary);
    border-bottom: 1px solid var(--bg-card-border);
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
    color: var(--text-secondary);
}

/* === ANIMATIONS === */
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

@keyframes confetti-fall {
    0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
    100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
}

.confetti {
    position: fixed;
    width: 10px;
    height: 10px;
    background: var(--primary);
    animation: confetti-fall 3s linear forwards;
    z-index: 9999;
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .navbar {
        padding: 0 1rem;
    }
    
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
    
    .sidebar.collapsed {
        transform: translateY(100%);
    }
    
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
    
    .sidebar-link span {
        display: none;
    }
    
    .main-content {
        margin-left: 0;
        padding: 1rem;
        padding-bottom: 100px;
        flex-direction: column;
    }
    
    .calendar-container {
        width: 100%;
        order: -1;
    }
    
    .calendar-widget {
        position: static;
        margin-bottom: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
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
        <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggle">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        
        <!-- Notification Dropdown -->
        <div class="notification-wrapper">
            <button class="notification-bell" id="notificationBell">
                <i class="bi bi-bell-fill"></i>
                <?php if($stats['pending_requests'] > 0): ?>
                <span class="notification-badge"><?php echo $stats['pending_requests']; ?></span>
                <?php endif; ?>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <?php if($pending_requests->num_rows > 0): ?>
                    <div class="dropdown-header">
                        <strong>Account Requests</strong>
                        <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addUser" class="btn-small">+ Add User</a>
                    </div>
                    <?php while($req = $pending_requests->fetch_assoc()): ?>
                    <div class="notification-item">
                        <div class="notification-content">
                            <strong><?php echo htmlspecialchars($req['nom'] . ' ' . $req['prenom']); ?></strong>
                            <p><?php echo htmlspecialchars($req['email']); ?></p>
                            <small><?php echo date('M d, H:i', strtotime($req['created_at'])); ?></small>
                        </div>
                            <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addUser?email=<?php echo urlencode($req['email']); ?>" class="btn-approve">Approve</a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="dropdown-empty">No new notifications</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="user-menu">
            <span>Farouk</span>
            <div class="user-avatar">F</div>
        </div>
    </div>
</nav>

<!-- === WRAPPER === -->
<div class="dashboard-wrapper">
    <!-- === SIDEBAR === -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="sidebar-link active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addUser" class="sidebar-link"><i class="bi bi-person-plus"></i><span>Add User</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/userList" class="sidebar-link"><i class="bi bi-people"></i><span>User List</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addModule" class="sidebar-link"><i class="bi bi-bookmark-plus"></i><span>Add Module</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/moduleList" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>Module List</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/classes" class="sidebar-link"><i class="bi bi-collection"></i><span>Manage Classes</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/assignStudents" class="sidebar-link"><i class="bi bi-person-check"></i><span>Assign Students</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/attendanceRecord" class="sidebar-link"><i class="bi bi-clipboard-data"></i><span>Attendance</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/notifications" class="sidebar-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main class="main-content">
        <!-- Main Content Area -->
        <div class="content-area">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div><span class="stat-label">Total Users</span><span class="stat-value" data-target="<?php echo $stats['total_users']; ?>">0</span></div>
                        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div><span class="stat-label">Total Modules</span><span class="stat-value" data-target="<?php echo $stats['total_modules']; ?>">0</span></div>
                        <div class="stat-icon"><i class="bi bi-book-half"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div><span class="stat-label">Today's Attendance</span><span class="stat-value" data-target="<?php echo $stats['today_attendance']; ?>">0</span></div>
                        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div><span class="stat-label">Pending Requests</span><span class="stat-value" data-target="<?php echo $stats['pending_requests']; ?>">0</span></div>
                        <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                    </div>
                </div>
            </div>

            <!-- Module Grid -->
            <div class="content-card">
                <div class="content-header">
                    <h3>Active Modules (Today's Attendance)</h3>
                    <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/moduleList" class="btn-primary">Manage Modules</a>
                </div>
                <div class="module-grid">
                    <?php while($mod = $modules->fetch_assoc()): ?>
                    <div class="module-item">
                        <div class="module-info">
                            <h4><?php echo htmlspecialchars($mod['module_name']); ?></h4>
                            <p><?php echo htmlspecialchars($mod['prof_nom'] . ' ' . $mod['prof_prenom']); ?></p>
                        </div>
                        <div class="module-stats">
                            <span class="attendance-count"><?php echo $mod['today_count']; ?></span>
                            <small>Present Today</small>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Recent Attendance -->
            <div class="content-card">
                <div class="content-header">
                    <h3>Recent Attendance Activity</h3>
                    <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/attendanceRecord" class="btn-primary">View Full Report</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Module</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($att = $recent_attendance->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($att['nom'] . ' ' . $att['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($att['module_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                            <td><?php echo $att['status'] === 'present' ? '✓ Present' : '✗ Absent'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Calendar Widget - Smaller, Right Side -->
        <div class="calendar-container">
            <div class="calendar-widget">
                <div class="calendar-header">
                    <button id="prevMonth"><i class="bi bi-chevron-left"></i></button>
                    <h4 id="currentMonth"></h4>
                    <button id="nextMonth"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="calendar-grid" id="calendarGrid"></div>
            </div>
        </div>
    </main>
</div>

<script>
// === THEME TOGGLE ===
const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const root = document.documentElement;

// Load saved theme or default to dark
const savedTheme = localStorage.getItem('theme') || 'dark';
if (savedTheme === 'light') {
    root.setAttribute('data-theme', 'light');
    themeIcon.className = 'bi bi-sun-fill';
}

themeToggle.addEventListener('click', () => {
    const currentTheme = root.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    if (newTheme === 'light') {
        root.setAttribute('data-theme', 'light');
        themeIcon.className = 'bi bi-sun-fill';
    } else {
        root.removeAttribute('data-theme');
        themeIcon.className = 'bi bi-moon-fill';
    }
    
    localStorage.setItem('theme', newTheme);
    
    // Smooth transition effect
    document.body.style.transition = 'background 0.5s ease';
});

// === SIDEBAR TOGGLE ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

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

// === STATS ANIMATION ===
function animateStats() {
    const stats = document.querySelectorAll('.stat-value');
    stats.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-target'));
        const duration = 2000;
        const start = performance.now();
        
        function update(currentTime) {
            const elapsed = currentTime - start;
            const progress = Math.min(elapsed / duration, 1);
            const value = Math.floor(target * progress);
            stat.textContent = value.toLocaleString();
            
            if(progress < 1) {
                requestAnimationFrame(update);
            }
        }
        requestAnimationFrame(update);
    });
}
animateStats();

// === CALENDAR ===
class Calendar {
    constructor() {
        this.currentDate = new Date();
        this.init();
    }

    init() {
        this.render();
        document.getElementById('prevMonth').addEventListener('click', () => this.changeMonth(-1));
        document.getElementById('nextMonth').addEventListener('click', () => this.changeMonth(1));
    }

    changeMonth(direction) {
        this.currentDate.setMonth(this.currentDate.getMonth() + direction);
        this.render();
    }

    render() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        document.getElementById('currentMonth').textContent = 
            this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });

        let grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(day => {
            const header = document.createElement('div');
            header.className = 'calendar-day';
            header.style.fontWeight = '700';
            header.style.color = 'var(--primary)';
            header.textContent = day;
            grid.appendChild(header);
        });

        for(let i = 0; i < firstDay; i++) {
            grid.appendChild(document.createElement('div'));
        }

        for(let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('div');
            dayEl.className = 'calendar-day';
            dayEl.textContent = day;
            
            if(day === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear()) {
                dayEl.classList.add('today');
            }
            
            if(Math.random() > 0.85) {
                dayEl.classList.add('has-event');
            }
            
            grid.appendChild(dayEl);
        }
    }
}

new Calendar();

// === CONFETTI ===
function triggerConfetti() {
    const colors = ['#00f5ff', '#7b2ff7', '#f72b7b', '#00e676', '#ffaa00'];
    for(let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
            document.body.appendChild(confetti);
            setTimeout(() => confetti.remove(), 5000);
        }, i * 30);
    }
}
</script>
</body>
</html>