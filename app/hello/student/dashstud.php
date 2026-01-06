<?php
// Bootstrap loads config and starts session; view is presentation-only.

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
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
<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// Make DB connection available
global $mysqli;

// Access control: only students
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

$student_id = $_SESSION['user_id'];

// Student stats
$stats = $mysqli->query(""
    SELECT 
        (SELECT COUNT(DISTINCT a.module_id) FROM attendance a WHERE a.student_id = $student_id) as total_modules,
        (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id AND status = 'absent') as total_absences,
        (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id) as total_classes,
        (SELECT COUNT(*) FROM modules m 
         INNER JOIN attendance a ON m.id = a.module_id 
         WHERE a.student_id = $student_id 
         AND DAYOFWEEK(CURDATE()) = DAYOFWEEK(a.date)) as today_classes
""")->fetch_assoc();

// Calculate attendance rate
$attendance_rate = $stats['total_classes'] > 0 
    ? round((($stats['total_classes'] - $stats['total_absences']) / $stats['total_classes']) * 100, 1) 
    : 0;

// Recent attendance
$recent_attendance = $mysqli->query(""
    SELECT m.module_name, a.date, a.status
    FROM attendance a
    JOIN modules m ON a.module_id = m.id
    WHERE a.student_id = $student_id
    ORDER BY a.date DESC
    LIMIT 10

");

// Enrolled modules with professors
// My Modules - Fixed without enrollment table

?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | macademia Faculty</title>
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

.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.02);
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

/* === CHATBOT PLACEHOLDER (CENTRAL) === */
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

/* === INFO PANEL (RIGHT SIDE) === */
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

.stat-row:last-child {
    border-bottom: none;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.stat-value {
    font-weight: 700;
    color: var(--text-primary);
}

.stat-value.high {
    color: var(--success);
}

.stat-value.medium {
    color: var(--warning);
}

.stat-value.low {
    color: var(--error);
}

/* Module list */
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

/* Schedule list */
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

/* === ANIMATIONS === */
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
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
    
    .info-panel {
        width: 100%;
        order: -1;
    }
    
    .chatbot-placeholder {
        height: 400px;
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
        <a href="student_dashboard.php" class="logo">
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

<!-- === WRAPPER === -->
<div class="dashboard-wrapper">
    <!-- === SIDEBAR === -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="student_dashboard.php" class="sidebar-link active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="my_attendance.php" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
            <li><a href="my_modules.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="my_absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
            <li><a href="chatbot.php" class="sidebar-link"><i class="bi bi-robot"></i><span>AI Assistant</span></a></li>
            <li><a href="profile.php" class="sidebar-link"><i class="bi bi-person-circle"></i><span>My Profile</span></a></li>
            <li><a href="logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main class="main-content">
        <!-- Chatbot Placeholder (Center) -->
        <div class="chatbot-container">
            <div class="chatbot-placeholder">
                <div class="chatbot-icon">
                    <i class="bi bi-robot"></i>
                </div>
                <h2>AI Assistant</h2>
                <p>Chatbot functionality coming soon.<br>Ask me about your attendance, modules, or schedule!</p>
            </div>
        </div>

        <!-- Info Panel (Right Side) -->
        <div class="info-panel">
            <!-- Attendance Stats -->
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

            <!-- My Modules -->
            <div class="info-card">
                <h3>My Modules</h3>
                <?php while($mod = $my_modules->fetch_assoc()): ?>
                <div class="module-item">
                    <div class="module-name"><?php echo htmlspecialchars($mod['module_name']); ?></div>
                    <div class="module-details">
                        <?php echo htmlspecialchars($mod['prof_prenom'] . ' ' . $mod['prof_nom']); ?> • 
                        <?php echo $mod['schedule_day'] . ' ' . $mod['schedule_time']; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Upcoming Schedule -->
            <div class="info-card">
                <h3>This Week's Schedule</h3>
                <?php if($schedule->num_rows > 0): ?>
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
// === SIDEBAR TOGGLE ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// === PULSE ANIMATION FOR CHATBOT ICON ===
function pulseChatbotIcon() {
    const icon = document.querySelector('.chatbot-icon');
    icon.style.transform = 'scale(1.1)';
    setTimeout(() => {
        icon.style.transform = 'scale(1)';
    }, 500);
}
setInterval(pulseChatbotIcon, 3000);
</script>
</body>
</html>