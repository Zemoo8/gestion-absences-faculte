<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// Load avatar helper
require_once __DIR__ . '/../../models/AvatarHelper.php';

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

// Get student info including photo
$student_info = $mysqli->query("SELECT nom, prenom, photo_path FROM users WHERE id = $student_id")->fetch_assoc();

// Student stats
$stats_result = $mysqli->query("SELECT 
    (SELECT COUNT(DISTINCT a.module_id) FROM attendance a WHERE a.student_id = $student_id) as total_modules,
    (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id AND status = 'absent') as total_absences,
    (SELECT COUNT(*) FROM attendance WHERE student_id = $student_id) as total_classes,
    (SELECT COUNT(*) FROM modules m 
     INNER JOIN attendance a ON m.id = a.module_id 
     INNER JOIN module_schedule ms ON m.id = ms.module_id
     WHERE a.student_id = $student_id 
     AND ms.weekday = DAYOFWEEK(CURDATE())) as today_classes
");
if ($stats_result && ($row = $stats_result->fetch_assoc())) {
    $stats = $row;
} else {
    $stats = [
        'total_modules' => 0,
        'total_absences' => 0,
        'total_classes' => 0,
        'today_classes' => 0
    ];
}

$attendance_rate = $stats['total_classes'] > 0 
    ? round((($stats['total_classes'] - $stats['total_absences']) / $stats['total_classes']) * 100, 1) 
    : 0;

// My Modules
$my_modules = $mysqli->query("
    SELECT m.module_name, u.prenom as prof_prenom, u.nom as prof_nom, ms.weekday, ms.start_time
    FROM modules m
    JOIN users u ON m.professor_id = u.id
    LEFT JOIN module_schedule ms ON m.id = ms.module_id
    WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
");
if ($my_modules === false) {
    $my_modules = false;
}

// Schedule
$schedule = $mysqli->query("
    SELECT m.module_name, ms.weekday, ms.start_time
    FROM modules m
    JOIN module_schedule ms ON m.id = ms.module_id
    WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
    ORDER BY ms.weekday, ms.start_time
");
if ($schedule === false) {
    $schedule = false;
}

$days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Student Dashboard | macademia Faculty</title>
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
        <button class="theme-toggle" id="themeToggle" title="Toggle theme">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        <div class="user-menu">
            <span><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
            <?php 
            $photo = isset($student_info['photo_path']) ? $student_info['photo_path'] : null;
            if($photo && file_exists(__DIR__ . '/../../../public/' . $photo)): 
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
            <li><a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="sidebar-link active"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
            <li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>


    <main class="main-content" style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 80vh; width: 100%;">
        <!-- Chatbot Card -->
        <div class="chatbot-placeholder" style="width:100%;max-width:900px;height:600px;background:var(--bg-card);backdrop-filter:var(--glass-blur);border:1px solid var(--bg-card-border);border-radius:20px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2.5rem;box-shadow:var(--shadow);position:relative;overflow:hidden;">
            <div style="width:100%;padding:0 2.5rem;">
                <div style="display:flex;align-items:center;gap:1.5rem;margin-bottom:2rem;">
                    <div style="width:80px;height:80px;background:var(--primary);border-radius:50%;display:grid;place-items:center;font-size:3rem;box-shadow:0 0 30px var(--primary-glow);overflow:hidden;"><img src="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : '/projet/Gestion-absences/public'; ?>/assets/robot.jpg" alt="AI Assistant" style="width:100%;height:100%;object-fit:cover;"></div>
                    <h2 style="font-size:2.5rem;font-weight:800;background:linear-gradient(135deg,var(--text-primary),var(--primary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin:0;">Student Assistant</h2>
                </div>
                <div id="chatLog" style="height:370px;overflow-y:auto;background:rgba(255,255,255,0.03);border-radius:16px;padding:1.2rem;margin-bottom:2rem;box-shadow:0 2px 8px rgba(0,0,0,0.10);font-size:1.25rem;">
                    <div class="message ai" style="display:flex;align-items:flex-start;gap:14px;">
                        <div class="message-avatar" style="width:45px;height:45px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;color:white;background:var(--secondary);font-size:2rem;overflow:hidden;"><img src="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : '/projet/Gestion-absences/public'; ?>/assets/robot.jpg" alt="AI" style="width:100%;height:100%;object-fit:cover;"></div>
                        <div class="message-content" style="max-width:70%;padding:16px 20px;border-radius:18px;line-height:1.6;white-space:pre-line;background:var(--bg-card);color:var(--text-primary);border-bottom-left-radius:7px;box-shadow:0 2px 8px rgba(0,0,0,0.12);font-size:1.15rem;">
                            Hi! Ask me about your absences, schedule, or modules.
                        </div>
                    </div>
                </div>
                <div class="input-wrapper" style="display:flex;gap:16px;">
                    <input type="text" id="userMessage" placeholder="Type your question here..." style="flex:1;padding:22px 28px;border:2px solid var(--bg-card-border);border-radius:32px;font-size:1.35rem;background:var(--bg-panel);color:var(--text-primary);outline:none;transition:border-color 0.3s;" onkeypress="handleEnter(event)">
                    <button id="sendBtn" style="padding:22px 40px;background:var(--primary);color:white;border:none;border-radius:32px;cursor:pointer;font-size:1.35rem;font-weight:800;transition:transform 0.2s,box-shadow 0.2s;" onclick="sendMessage()">Send</button>
                </div>
            </div>
        </div>

        <!-- My Schedule Card -->
        <div class="info-card" style="width:100%;max-width:900px;margin-top:2rem;box-shadow:var(--shadow);background:var(--bg-card);border-radius:20px;padding:2rem 2.5rem;">
            <h3 style="font-size:1.7rem;font-weight:700;color:var(--primary);margin-bottom:1.2rem;">My Schedule</h3>
            <?php if($schedule && $schedule->num_rows > 0): ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.2rem;">
                <?php while($sch = $schedule->fetch_assoc()): ?>
                    <div class="schedule-item" style="background:rgba(0,245,255,0.08);border-radius:12px;padding:1rem 1.2rem;">
                        <div class="schedule-time" style="font-weight:700;color:var(--primary);font-size:1.1rem;">
                            <?php echo ($days[(int)$sch['weekday']] ?? $sch['weekday']) . ' ' . $sch['start_time']; ?>
                        </div>
                        <div class="schedule-name" style="color:var(--text-secondary);font-size:1rem;">
                            <?php echo htmlspecialchars($sch['module_name']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); font-size: 1.1rem;">No classes scheduled this week</p>
            <?php endif; ?>
        </div>
        <script>
        const chatLog = document.getElementById('chatLog');
        const userInput = document.getElementById('userMessage');
        const sendBtn = document.getElementById('sendBtn');
        const studentPhoto = "<?php echo (!empty($student_info['photo_path']) ? $student_info['photo_path'] : ''); ?>";
        const publicUrl = "<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : '/projet/Gestion-absences/public'; ?>";

        function handleEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'ai'}`;
            messageDiv.style.display = 'flex';
            messageDiv.style.alignItems = 'flex-start';
            messageDiv.style.gap = '14px';
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.style.width = '45px';
            avatar.style.height = '45px';
            avatar.style.borderRadius = '50%';
            avatar.style.display = 'flex';
            avatar.style.alignItems = 'center';
            avatar.style.justifyContent = 'center';
            avatar.style.fontWeight = 'bold';
            avatar.style.color = 'white';
            avatar.style.background = isUser ? 'var(--primary)' : 'var(--secondary)';
            avatar.style.fontSize = '2rem';
            avatar.style.overflow = 'hidden';
            if (isUser) {
                if (studentPhoto) {
                    const img = document.createElement('img');
                    img.src = publicUrl + '/' + studentPhoto;
                    img.alt = 'Your Profile';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    avatar.appendChild(img);
                } else {
                    avatar.textContent = '👤';
                }
            } else {
                const img = document.createElement('img');
                img.src = '/projet/Gestion-absences/public/assets/robot.jpg';
                img.alt = 'AI';
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                avatar.appendChild(img);
            }
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = content;
            contentDiv.style.maxWidth = '70%';
            contentDiv.style.padding = '16px 20px';
            contentDiv.style.borderRadius = '18px';
            contentDiv.style.lineHeight = '1.6';
            contentDiv.style.whiteSpace = 'pre-line';
            contentDiv.style.background = isUser ? 'var(--primary)' : 'var(--bg-card)';
            contentDiv.style.color = isUser ? 'white' : 'var(--text-primary)';
            contentDiv.style.borderBottomRightRadius = isUser ? '7px' : '';
            contentDiv.style.borderBottomLeftRadius = !isUser ? '7px' : '';
            contentDiv.style.boxShadow = '0 2px 8px rgba(0,0,0,0.12)';
            contentDiv.style.fontSize = '1.15rem';
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(contentDiv);
            chatLog.appendChild(messageDiv);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message ai';
            typingDiv.id = 'typing-indicator';
            typingDiv.style.display = 'flex';
            typingDiv.style.alignItems = 'flex-start';
            typingDiv.style.gap = '14px';
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.style.background = 'var(--secondary)';
            avatar.style.width = '45px';
            avatar.style.height = '45px';
            avatar.style.borderRadius = '50%';
            avatar.style.display = 'flex';
            avatar.style.alignItems = 'center';
            avatar.style.justifyContent = 'center';
            avatar.style.fontWeight = 'bold';
            avatar.style.color = 'white';
            avatar.style.fontSize = '2rem';
            avatar.style.overflow = 'hidden';
            const img = document.createElement('img');
            img.src = '/projet/Gestion-absences/public/assets/robot.jpg';
            img.alt = 'AI';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            avatar.appendChild(img);
            const typingContent = document.createElement('div');
            typingContent.className = 'message-content typing-indicator';
            typingContent.innerHTML = '<span></span><span></span><span></span>';
            typingContent.style.background = 'var(--bg-card)';
            typingContent.style.color = 'var(--text-primary)';
            typingContent.style.padding = '16px 20px';
            typingContent.style.borderRadius = '18px';
            typingContent.style.fontSize = '1.15rem';
            typingDiv.appendChild(avatar);
            typingDiv.appendChild(typingContent);
            chatLog.appendChild(typingDiv);
            chatLog.scrollTop = chatLog.scrollHeight;
        }

        function removeTyping() {
            const typing = document.getElementById('typing-indicator');
            if (typing) {
                typing.remove();
            }
        }

        async function sendMessage() {
            const msg = userInput.value.trim();
            if (!msg) return;
            userInput.disabled = true;
            sendBtn.disabled = true;
            addMessage(msg, true);
            userInput.value = '';
            showTyping();
            try {
                const response = await fetch('/projet/Gestion-absences/public/api/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        message: msg,
                        email: "<?php echo $_SESSION['email'] ?? ''; ?>"
                    })
                });
                removeTyping();
                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }
                const data = await response.json();
                addMessage(data.answer || "Sorry, I couldn't process that.");
            } catch (err) {
                removeTyping();
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = `Error: ${err.message}. Please make sure the backend server is running.`;
                chatLog.appendChild(errorDiv);
            } finally {
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
            }
        }
        userInput && userInput.focus();
        </script>


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