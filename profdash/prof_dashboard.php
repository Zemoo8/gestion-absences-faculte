<?php
session_start();
require_once '../login/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../login/PHPMailer/src/Exception.php';
require '../login/PHPMailer/src/PHPMailer.php';
require '../login/PHPMailer/src/SMTP.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];

// Get professor modules
$modules = $mysqli->prepare("
    SELECT m.id, m.module_name, m.total_hours,
           COUNT(DISTINCT a.date) as total_sessions,
           COUNT(DISTINCT a.student_id) as enrolled_students
    FROM modules m
    LEFT JOIN attendance a ON m.id = a.module_id
    WHERE m.professor_id = ?
    GROUP BY m.id
");
$modules->bind_param("i", $prof_id);
$modules->execute();
$modules_result = $modules->get_result();

// Get at-risk students (>20% absence)
$at_risk = $mysqli->prepare("
    SELECT u.nom, u.prenom, u.email, m.module_name,
           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absences,
           COUNT(CASE WHEN a.status IS NOT NULL THEN 1 END) as total_recorded,
           m.total_hours,
           (COUNT(CASE WHEN a.status = 'absent' THEN 1 END) / m.total_hours * 100) as absence_rate
    FROM users u
    JOIN attendance a ON u.id = a.student_id
    JOIN modules m ON a.module_id = m.id
    WHERE m.professor_id = ? AND u.role = 'student'
    GROUP BY u.id, m.id
    HAVING absence_rate > 20
    ORDER BY absence_rate DESC
");
$at_risk->bind_param("i", $prof_id);
$at_risk->execute();
$at_risk_result = $at_risk->get_result();

// Check if can take attendance now
function canTakeAttendance($module_id) {
    global $mysqli;
    date_default_timezone_set('Africa/Tunis');
    $current_time = date('H:i:s');
    $current_day = date('w');
    
    $stmt = $mysqli->prepare("
        SELECT COUNT(*) as count 
        FROM module_schedule 
        WHERE module_id = ? AND weekday = ? 
        AND start_time <= ? AND end_time >= ?
    ");
    $stmt->bind_param("isss", $module_id, $current_day, $current_time, $current_time);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Professor Dashboard | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root {
    --primary: #00f5ff;
    --primary-glow: rgba(0, 245, 255, 0.5);
    --accent: #f72b7b;
    --error: #ff3b3b;
    --success: #00e676;
    --bg-main: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%);
    --bg-panel: rgba(10, 14, 39, 0.7);
    --bg-card: rgba(255, 255, 255, 0.04);
    --bg-card-border: rgba(255, 255, 255, 0.08);
    --text-primary: #f0f4f8;
    --text-secondary: #cbd5e1;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', sans-serif;
    background: var(--bg-main);
    color: var(--text-primary);
    min-height: 100vh;
}
.dashboard-wrapper { display: flex; margin-top: 70px; }
.sidebar {
    width: 280px;
    background: var(--bg-panel);
    border-right: 1px solid var(--bg-card-border);
    position: fixed;
    left: 0;
    top: 70px;
    height: calc(100vh - 70px);
    padding: 1.5rem 0;
}
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
.stat-card, .module-card {
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 1.75rem;
    transition: var(--transition);
}
.stat-card:hover, .module-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary);
}
.alert-banner {
    background: rgba(255, 59, 59, 0.1);
    border: 1px solid rgba(255, 59, 59, 0.3);
    border-radius: 14px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.attendance-btn {
    width: 100%;
    padding: 0.75rem;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
}
.attendance-btn.enabled {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
}
.attendance-btn.disabled {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-secondary);
    cursor: not-allowed;
}
.module-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem; }
.sidebar-menu { list-style: none; }
.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition);
}
.sidebar-menu a.active { color: var(--primary); }
.sidebar-menu i { font-size: 1.25rem; }
</style>
</head>
<body>
<div class="dashboard-wrapper">
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li><a href="prof_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="take_attendance.php"><i class="bi bi-qr-code"></i> Quick Attendance</a></li>
            <li><a href="my_modules.php"><i class="bi bi-bookshelf"></i> My Modules</a></li>
            <li><a href="students.php"><i class="bi bi-people"></i> Students</a></li>
            <li><a href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <?php if ($at_risk_result->num_rows > 0): ?>
        <div class="alert-banner">
            <i class="bi bi-exclamation-triangle-fill" style="color: var(--error);"></i>
            <div>
                <strong><?php echo $at_risk_result->num_rows; ?> students exceed 20% absence rate</strong><br>
                <small style="color: var(--text-secondary);">Email notifications sent automatically</small>
            </div>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem;">Active Modules</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);"><?php echo $modules_result->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem;">At-Risk Students</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--error);"><?php echo $at_risk_result->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3 style="color: var(--text-secondary); font-size: 0.875rem;">Today's Classes</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--success);">--</div>
            </div>
        </div>

        <h2 style="margin-bottom: 1.5rem;">My Modules</h2>
        <div class="module-grid">
            <?php while ($mod = $modules_result->fetch_assoc()): ?>
            <?php $can_mark = canTakeAttendance($mod['id']); ?>
            <div class="module-card">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h3 style="font-size: 1.25rem;"><?php echo htmlspecialchars($mod['module_name']); ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.875rem;">
                            <?php echo $mod['total_sessions']; ?> sessions • <?php echo $mod['enrolled_students']; ?> students
                        </p>
                    </div>
                    <span style="color: var(--primary); font-size: 0.75rem; background: rgba(0,245,255,0.1); padding: 0.25rem 0.5rem; border-radius: 6px;">
                        <?php echo $can_mark ? 'LIVE' : 'CLOSED'; ?>
                    </span>
                </div>
                
                <button class="attendance-btn <?php echo $can_mark ? 'enabled' : 'disabled'; ?>" 
                        <?php echo $can_mark ? "onclick=\"location.href='take_attendance.php?module={$mod['id']}'\"" : "disabled"; ?>>
                    <i class="bi bi-camera"></i> <?php echo $can_mark ? 'Take Attendance Now' : 'Outside Class Hours'; ?>
                </button>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>
</body>
</html>