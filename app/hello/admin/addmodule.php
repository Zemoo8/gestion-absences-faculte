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

// Pending requests for notification bell
$pending_requests = $mysqli->query("
    SELECT id, nom, prenom, email, created_at 
    FROM account_requests 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5"
);

$msg = "";
$created_module_details = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST['module_name']);
    $professor_id = (int)$_POST['professor_id'];
    $weekday = $_POST['weekday'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $class_ids = $_POST['class_ids'] ?? [];

    if(empty($name) || empty($professor_id) || empty($weekday) || empty($start_time) || empty($end_time) || empty($class_ids)) {
        $msg = "<div class='msg-box msg-error'><i class='bi bi-exclamation-circle'></i>All fields required!</div>";
    } else {
        // Insert module
        $stmt = $mysqli->prepare("INSERT INTO modules (module_name, professor_id) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $professor_id);
        if($stmt->execute()){
            $module_id = $stmt->insert_id;
            
            // Insert schedule
            $stmt_schedule = $mysqli->prepare("INSERT INTO module_schedule (module_id, weekday, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt_schedule->bind_param("iiss", $module_id, $weekday, $start_time, $end_time);
            $stmt_schedule->execute();
            
            // Link to classes
            $stmt2 = $mysqli->prepare("INSERT INTO module_classes (module_id, class_id) VALUES (?, ?)");
            foreach($class_ids as $class_id){
                $stmt2->bind_param("ii", $module_id, $class_id);
                $stmt2->execute();
            }
            
            // Get details for display
            $day_names = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $day_name = $day_names[$weekday] ?? '';
            $created_module_details = "<div style='margin-top:1rem; padding:1rem; background:rgba(0,245,255,0.05); border-radius:10px; border:1px solid var(--bg-card-border);'>";
            $created_module_details .= "<strong style='color:var(--primary);'>Schedule:</strong> $day_name, $start_time - $end_time<br>";
            $created_module_details .= "<strong style='color:var(--primary);'>Classes:</strong> " . count($class_ids) . " class(es) assigned";
            $created_module_details .= "</div>";
            
            $msg = "<div class='msg-box msg-success'><i class='bi bi-check-circle'></i>✅ Module created successfully! $created_module_details</div>";
        } else {
            $msg = "<div class='msg-box msg-error'><i class='bi bi-x-circle'></i>Error adding module.</div>";
        }
    }
}

// Get data for dropdowns
$professors = $mysqli->query("SELECT id, nom, prenom FROM users WHERE role='professor' ORDER BY nom");
$classes = $mysqli->query("SELECT id, class_name FROM classes ORDER BY class_name");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Add Module | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --primary: #00f5ff; --primary-glow: rgba(0, 245, 255, 0.5);
    --secondary: #7b2ff7; --accent: #f72b7b;
    --bg-main: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%);
    --bg-panel: rgba(10, 14, 39, 0.7);
    --bg-card: rgba(255, 255, 255, 0.04);
    --bg-card-border: rgba(255, 255, 255, 0.08);
    --text-primary: #f0f4f8; --text-secondary: #cbd5e1;
    --error: #ff3b3b; --success: #00e676;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    --glass-blur: blur(24px) saturate(200%);
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
    position: absolute; top: 100%; right: 0; margin-top: 1rem; backdrop-filter: none;
    border: 1px solid var(--bg-card-border); border-radius: 14px; min-width: 320px;
    max-width: 400px; box-shadow: var(--shadow); opacity: 1; visibility: hidden;
    transform: translateY(-10px); transition: var(--transition); z-index: 1001;
}
.notification-dropdown.show {
    background: #0a0e27; opacity: 1; visibility: visible; transform: translateY(0);
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
.sidebar-link:hover {
    color: var(--primary); background: rgba(255, 255, 255, 0.04);
}
.sidebar-link i { font-size: 1.25rem; width: 24px; text-align: center; }
.main-content {
    flex: 1; margin-left: 280px; padding: 2rem; transition: var(--transition);
}
.sidebar.collapsed ~ .main-content { margin-left: 0; }
.container { max-width: 600px; margin: 0 auto; }
h2 { color: var(--primary); margin-bottom: 1.5rem; }
.form-box {
    background: var(--bg-card); border: 1px solid var(--bg-card-border); border-radius: 20px;
    padding: 2rem; margin-bottom: 2rem;
}
.form-box select, .form-box .class-checkboxes {
    width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 2px solid var(--bg-card-border);
    border-radius: 10px; background: #12172f; color: #f0f4f8;
}
.class-checkboxes { max-height: 150px; overflow-y: auto; background: #12172f; border: 2px solid var(--bg-card-border); border-radius: 10px; padding: 0.75rem; }
.class-checkboxes label { display: block; margin-bottom: 0.5rem; color: var(--text-primary); }
.btn-primary {
    background: linear-gradient(135deg, var(--primary), #7b2ff7); color: white;
    padding: 0.75rem 2rem; border: none; border-radius: 12px; font-weight: 700; cursor: pointer;
}
.btn-secondary {
    background: rgba(255,255,255,0.1); color: var(--text-primary); padding: 0.5rem 1rem;
    border-radius: 8px; text-decoration: none; display: inline-block; margin-bottom: 1rem;
}

/* ===== PROFESSIONAL FORM STYLES ===== */
.page-header {
    margin-bottom: 2.5rem;
}

.page-title h2 {
    margin-bottom: 0.5rem;
    font-size: 2rem;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.5;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--bg-card-border);
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.9rem;
}

.form-label i {
    font-size: 1.1rem;
    color: var(--primary);
}

.form-box input[type="text"],
.form-box select,
.form-box input[type="time"] {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 2px solid var(--bg-card-border);
    border-radius: 10px;
    background: #12172f;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: var(--transition);
}

.form-box input[type="text"]:focus,
.form-box select:focus,
.form-box input[type="time"]:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0, 245, 255, 0.1);
}

.checkbox-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    margin-bottom: 0.5rem;
}

.checkbox-item:hover {
    background: rgba(255, 255, 255, 0.03);
}

.checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
}

.form-helper {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.85rem 2rem;
    font-size: 1rem;
    transition: var(--transition);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 245, 255, 0.3);
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    padding: 0.6rem 1.2rem;
    border: 1px solid var(--bg-card-border);
    transition: var(--transition);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(-4px);
}

/* Enhanced message styling */
.msg-box {
    padding: 1rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.msg-success {
    background: rgba(0, 230, 118, 0.1);
    border: 1px solid rgba(0, 230, 118, 0.2);
    color: var(--success);
}

.msg-error {
    background: rgba(255, 59, 59, 0.1);
    border: 1px solid rgba(255, 59, 59, 0.2);
    color: var(--error);
}

.msg-box i {
    font-size: 1.2rem;
}

/* NEW: Schedule fields layout */
.schedule-fields {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .schedule-fields {
        grid-template-columns: 1fr;
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
                <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
                <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addUser" class="sidebar-link"><i class="bi bi-person-plus"></i><span>Add User</span></a></li>
                <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/userList" class="sidebar-link"><i class="bi bi-people"></i><span>User List</span></a></li>
                <li><a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/addModule" class="sidebar-link active"><i class="bi bi-bookmark-plus"></i><span>Add Module</span></a></li>
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
            <!-- Enhanced Page Header -->
            <div class="page-header">
                <a href="<?php echo PUBLIC_URL; ?>/index.php/admindash/dashboard" class="btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <div class="page-title">
                    <h2>Add New Module</h2>
                    <p class="page-subtitle">Create and assign academic modules to professors and classes</p>
                </div>
            </div>

            <!-- Enhanced Message Display -->
            <?= $msg ?>
            
            <!-- Professional Form Layout -->
            <form method="POST" class="form-box">
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label" for="module_name">
                            <i class="bi bi-bookmark-plus"></i> Module Name
                        </label>
                        <input type="text" name="module_name" id="module_name" placeholder="Enter module name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="professor_id">
                            <i class="bi bi-person-badge"></i> Assigned Professor
                        </label>
                        <select name="professor_id" id="professor_id" required>
                            <option value="">Select Professor</option>
                            <?php while($p = $professors->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nom'] . ' ' . $p['prenom']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- NEW SCHEDULE SECTION -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-calendar3"></i> Schedule
                        </label>
                        <div class="schedule-fields">
                            <div>
                                <label class="form-label" for="weekday" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                                    Day of Week
                                </label>
                                <select name="weekday" id="weekday" required>
                                    <option value="">Select Day</option>
                                    <option value="1">Monday</option>
                                    <option value="2">Tuesday</option>
                                    <option value="3">Wednesday</option>
                                    <option value="4">Thursday</option>
                                    <option value="5">Friday</option>
                                    <option value="6">Saturday</option>
                                    <option value="7">Sunday</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="start_time" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                                    Start Time
                                </label>
                                <input type="time" name="start_time" id="start_time" required>
                            </div>
                            <div>
                                <label class="form-label" for="end_time" style="font-size: 0.8rem; margin-bottom: 0.5rem;">
                                    End Time
                                </label>
                                <input type="time" name="end_time" id="end_time" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <label class="form-label">
                        <i class="bi bi-collection"></i> Target Classes
                    </label>
                    <div class="class-checkboxes">
                        <?php while($c = $classes->fetch_assoc()): ?>
                        <label class="checkbox-item">
                            <input type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>">
                            <span class="checkmark"></span>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </label>
                        <?php endwhile; ?>
                    </div>
                    <small class="form-helper">Select at least one class for this module</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="bi bi-check-circle"></i> Create Module
                    </button>
                </div>
            </form>
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