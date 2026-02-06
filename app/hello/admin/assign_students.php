<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// Make DB connection available
global $mysqli;

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

// Admin info for navbar avatar
$admin_id = (int)$_SESSION['user_id'];
$admin_info = $mysqli->query("SELECT nom, prenom, photo_path FROM users WHERE id = $admin_id")->fetch_assoc();
$public_url = defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost';
$admin_photo = null;
if ($admin_info && !empty($admin_info['photo_path'])) {
    if (file_exists(__DIR__ . '/../../../public/' . $admin_info['photo_path'])) {
        $admin_photo = $public_url . '/' . $admin_info['photo_path'];
    }
}

// Pending requests for notification bell
$pending_requests = $mysqli->query("
    SELECT id, nom, prenom, email, created_at 
    FROM account_requests 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5"
);

$msg = "";

// Assigner des étudiants à une classe
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $class_id = (int)$_POST['class_id'];
    $student_ids = $_POST['student_ids'] ?? [];

    if(empty($class_id) || empty($student_ids)){
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Please select a class and at least one student!</div>";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO student_classes (student_id, class_id) VALUES (?, ?)");
        foreach($student_ids as $student_id){
            $stmt->bind_param("ii", $student_id, $class_id);
            $stmt->execute();
        }
        $msg = "<div style='color:#00e676;margin-bottom:1rem;'>✅ Students assigned to class!</div>";
    }
}

// Récupère les classes et les étudiants non-assignés
$classes = $mysqli->query("SELECT id, class_name FROM classes ORDER BY class_name");
$students = $mysqli->query("
    SELECT id, nom, prenom 
    FROM users 
    WHERE role='student' 
    AND id NOT IN (SELECT student_id FROM student_classes)
    ORDER BY nom, prenom
");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Assign Students | macademia Faculty</title>
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
    background: var(--bg-main); background-size: 400% 400%;
    animation: gradientShift 25s ease infinite; color: var(--text-primary);
    overflow-x: hidden; min-height: 100vh;
}
@keyframes gradientShift { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
.navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 70px;
    background: var(--bg-panel); backdrop-filter: var(--glass-blur);
    border-bottom: 1px solid var(--bg-card-border); display: flex; align-items: center;
    padding: 0 2rem; justify-content: space-between;
}
.navbar-left { display: flex; align-items: center; gap: 1rem; }
.sidebar-toggle {
    background: none; border: none; color: var(--primary); font-size: 1.5rem;
    cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: var(--transition);
}
.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.05); }
.logo {
    display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-primary);
}
.logo-icon {
    width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 10px; display: grid; place-items: center; font-size: 1.25rem;
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 20px var(--primary-glow); }
    50% { transform: scale(1.08); box-shadow: 0 0 40px var(--primary-glow); }
}
.logo h1 {
    font-size: 1.5rem; font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.navbar-right { display: flex; align-items: center; gap: 1.5rem; }
.notification-wrapper { position: relative; }
.notification-bell {
    background: none; border: none; color: var(--text-secondary); font-size: 1.25rem;
    cursor: pointer; padding: 0.5rem; border-radius: 50%; transition: var(--transition);
    position: relative;
}
.notification-bell:hover { background: rgba(255, 255, 255, 0.05); color: var(--primary); }
.notification-badge {
    position: absolute; top: 2px; right: 2px; background: var(--error); color: white;
    font-size: 0.6rem; font-weight: 700; width: 16px; height: 16px; border-radius: 50%;
    display: grid; place-items: center; animation: pulseBadge 2s ease infinite;
}
.notification-dropdown {
    position: absolute; top: 100%; right: 0; margin-top: 1rem; backdrop-filter: var(--glass-blur);
    background: var(--bg-panel); border: 1px solid var(--bg-card-border); border-radius: 14px; min-width: 320px;
    max-width: 400px; box-shadow: var(--shadow); opacity: 1; visibility: hidden;
    transform: translateY(-10px); transition: var(--transition); z-index: 1001;
}
.notification-dropdown.show {
    opacity: 1; visibility: visible; transform: translateY(0);
}
.dropdown-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem; border-bottom: 1px solid var(--bg-card-border);
}
.btn-small {
    background: linear-gradient(135deg, var(--primary), var(--accent)); color: white;
    padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.75rem; font-weight: 600;
    text-decoration: none;
}
.notification-item {
    padding: 1rem; border-bottom: 1px solid var(--bg-card-border);
    display: flex; justify-content: space-between; align-items: center; transition: var(--transition);
}
.notification-item:hover { background: rgba(255, 255, 255, 0.03); }
.notification-content strong { color: var(--primary); font-size: 0.875rem; }
.notification-content p { margin: 0.25rem 0; font-size: 0.8rem; color: var(--text-secondary); }
.notification-content small { color: var(--text-muted); font-size: 0.75rem; }
.btn-approve {
    background: rgba(0, 230, 118, 0.1); color: var(--success); padding: 0.4rem 0.8rem;
    border-radius: 8px; font-size: 0.75rem; font-weight: 600; text-decoration: none;
    border: 1px solid rgba(0, 230, 118, 0.2);
}
.dropdown-empty { padding: 1rem; text-align: center; color: var(--text-muted); }
.user-menu { display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-weight: 600; }
.user-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, var(--secondary), var(--accent));
    display: grid; place-items: center; font-size: 1rem; font-weight: 700;
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
.dashboard-wrapper { display: flex; min-height: 100vh; margin-top: 70px; }
.sidebar {
    width: 280px; background: var(--bg-panel); border-right: 1px solid var(--bg-card-border);
    padding: 1.5rem 0; position: fixed; left: 0; top: 70px;
    height: calc(100vh - 70px); overflow-y: auto; transition: var(--transition); z-index: 999;
}
.sidebar.collapsed { transform: translateX(-100%); }
.sidebar-menu { list-style: none; }
.sidebar-item { margin-bottom: 0.25rem; }
.sidebar-link {
    display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem;
    color: var(--text-secondary); text-decoration: none; font-weight: 500;
    border-radius: 0 12px 12px 0; transition: var(--transition);
}
.sidebar-link.active {
    color: var(--primary); background: rgba(255, 255, 255, 0.04);
}
:root[data-theme="light"] .sidebar-link.active {
    background: rgba(139, 94, 60, 0.08);
}
.sidebar-link:hover {
    color: var(--primary); background: rgba(255, 255, 255, 0.04);
}
:root[data-theme="light"] .sidebar-link:hover {
    background: rgba(139, 94, 60, 0.08);
}
.sidebar-link i { font-size: 1.25rem; width: 24px; text-align: center; }
.main-content {
    flex: 1; margin-left: 280px; padding: 2rem; transition: var(--transition);
}
.sidebar.collapsed ~ .main-content { margin-left: 0; }
.container { max-width: 800px; margin: 0 auto; }
h2 { color: var(--primary); margin-bottom: 1.5rem; }
.form-box {
    background: var(--bg-card); border: 1px solid var(--bg-card-border); border-radius: 20px;
    padding: 2rem; margin-bottom: 2rem;
}
.form-box select, .form-box .student-checkboxes {
    width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 2px solid var(--bg-card-border);
    border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: var(--text-primary); transition: var(--transition);
}

:root[data-theme="light"] .form-box select,
:root[data-theme="light"] .form-box .student-checkboxes {
    background: #ffffff;
    border: 2px solid rgba(0, 0, 0, 0.12);
    color: #2b2b2b;
}

.form-box select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-glow);
}

.student-checkboxes { max-height: 200px; overflow-y: auto; }
.student-checkboxes label { display: block; margin-bottom: 0.5rem; }
.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;
    padding: 0.75rem 2rem; border: none; border-radius: 12px; font-weight: 700; cursor: pointer;
    transition: var(--transition);
}

:root[data-theme="light"] .btn-primary {
    background: linear-gradient(135deg, #8B5E3C, #6B4E2C);
    box-shadow: 0 2px 8px rgba(139, 94, 60, 0.15);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px var(--primary-glow);
}

.btn-secondary {
    background: rgba(255,255,255,0.1); color: var(--text-primary); padding: 0.5rem 1rem;
    border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 1rem;
    transition: var(--transition);
}

:root[data-theme="light"] .btn-secondary {
    background: rgba(139, 94, 60, 0.08);
    border: 1px solid rgba(139, 94, 60, 0.2);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.15);
}

:root[data-theme="light"] .btn-secondary:hover {
    background: rgba(139, 94, 60, 0.15);
}
.class-list {
    background: var(--bg-card); border: 1px solid var(--bg-card-border);
    border-radius: 20px; padding: 1.5rem;
}
.class-item { padding: 1rem; border-bottom: 1px solid var(--bg-card-border); }
.class-item:last-child { border-bottom: none; }
.class-item strong { color: var(--primary); }
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
                <?php if($pending_requests->num_rows > 0): ?>
                <span class="notification-badge"><?php echo $pending_requests->num_rows; ?></span>
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
            <span><?php echo htmlspecialchars($admin_info['prenom'] . ' ' . $admin_info['nom']); ?></span>
            <?php if ($admin_photo): ?>
                <img src="<?php echo htmlspecialchars($admin_photo); ?>" alt="Profile" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; object-position: center;">
            <?php else: ?>
                <div class="user-avatar"><?php echo substr($admin_info['prenom'], 0, 1); ?></div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- === WRAPPER === -->
<div class="dashboard-wrapper">
    <!-- === SIDEBAR === -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/profile" class="sidebar-link"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
            <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
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
        <div class="container">
            <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="btn-secondary">← Back to Dashboard</a>
            <h2>Assign Students to Classes</h2>
            <?= $msg ?>

            <!-- Form -->
            <form method="POST" class="form-box">
                <select name="class_id" required>
                    <option value="">Select Class</option>
                    <?php while($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <label style="color:var(--text-primary);margin-bottom:0.5rem;">Select Students (unassigned only):</label>
                <div class="student-checkboxes">
                    <?php while($s = $students->fetch_assoc()): ?>
                    <label>
                        <input type="checkbox" name="student_ids[]" value="<?= $s['id'] ?>">
                        <?= htmlspecialchars($s['nom'] . ' ' . $s['prenom']) ?>
                    </label>
                    <?php endwhile; ?>
                </div>

                <button type="submit" class="btn-primary">Assign Students</button>
            </form>

            <!-- Current Assignments -->
            <div class="class-list">
                <h3 style="color:var(--primary);margin-bottom:1rem;">Current Assignments</h3>
                <?php
                $assigned = $mysqli->query("
                    SELECT c.class_name, u.nom, u.prenom 
                    FROM student_classes sc
                    JOIN classes c ON sc.class_id = c.id
                    JOIN users u ON sc.student_id = u.id
                    ORDER BY c.class_name, u.nom
                ");
                $current_class = "";
                while($a = $assigned->fetch_assoc()){
                    if($current_class != $a['class_name']){
                        echo "<div class='class-item'><strong>".htmlspecialchars($a['class_name'])."</strong><br>";
                        $current_class = $a['class_name'];
                    }
                    echo htmlspecialchars($a['nom'] . ' ' . $a['prenom']) . "<br>";
                }
                ?>
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
    document.body.style.transition = 'background 0.5s ease';
});

// === SIDEBAR TOGGLE ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// === SET ACTIVE SIDEBAR LINK ===
const currentPath = window.location.pathname;
const sidebarLinks = document.querySelectorAll('.sidebar-link');
sidebarLinks.forEach(link => {
    if (link.href.includes('assignStudents')) {
        link.classList.add('active');
    }
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
</script>

</body>
</html>