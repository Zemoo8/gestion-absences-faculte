<?php
// Bootstrap loads config and starts session; view remains presentation-only.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// If accessed directly, redirect to front-controller professor students route
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
    $base = defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    header('Location: ' . $base . '/index.php/profdash/students');
    exit();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

$prof_id = (int)$_SESSION['user_id'];
$prof_info = $mysqli->query("SELECT nom, prenom FROM users WHERE id = $prof_id")->fetch_assoc();

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* FIXED ABSENCE RATE: Each absence = 3.3% */
$absence_per_session = 3.3;

/* Reminder log table */
$mysqli->query("CREATE TABLE IF NOT EXISTS reminder_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    professor_id INT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_student_prof (student_id, professor_id),
    KEY idx_date (sent_at)
)");

/* ---------- AUTO SEND REMINDER FUNCTION ---------- */
function autoSendReminder($student_id, $module_id, $prof_id, $prof_info, $mysqli, $absence_per_session) {
    // Count absences for this student in this module
    $stat = $mysqli->query("
        SELECT COUNT(*) as absence_count, u.email, u.nom, u.prenom
        FROM attendance a
        JOIN users u ON u.id = a.student_id
        WHERE a.student_id = $student_id
          AND a.module_id = $module_id
          AND a.status = 'absent'
          AND a.module_id IN (SELECT id FROM modules WHERE professor_id = $prof_id)
    ")->fetch_assoc();

    if (!$stat || $stat['absence_count'] == 0) {
        return false;
    }

    // Calculate absence rate
    $absRate = $stat['absence_count'] * $absence_per_session;

    // Only send if absence rate >= 20%
    if ($absRate <= 0) {
        return false;
    }

    // Check if already sent today
    $today = date('Y-m-d');
    $already = $mysqli->query("SELECT 1 FROM reminder_log WHERE student_id=$student_id AND professor_id=$prof_id AND DATE(sent_at)='$today'")->num_rows;
    if ($already) {
        return false;
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'farouk.zemoo@gmail.com';
        $mail->Password = 'kibh ehzs ofxg zpem';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty System');
        $mail->addAddress($stat['email'], $stat['prenom'].' '.$stat['nom']);
        $mail->isHTML(true);
        $mail->Subject = 'Attendance Reminder';
        $mail->Body = '
        <div style="font-family:Inter,sans-serif;max-width:600px;margin:auto;background:#0a0e27;color:#f0f4f8;border-radius:12px;overflow:hidden">
          <div style="padding:30px;">
            <h2 style="color:#00f5ff;margin-top:0;">Attendance Alert</h2>
            <p>Hi <strong>'.htmlspecialchars($stat['prenom']).'</strong>,</p>
            <p>Your absence rate is <strong>'.round($absRate,1).'%</strong> ('.$stat['absence_count'].' absences) in this module taught by Prof. '.htmlspecialchars($prof_info['prenom'].' '.$prof_info['nom']).'.</p>
            <p>Please attend upcoming classes to avoid academic penalties.</p>
          </div>
        </div>';

        $mail->send();
        $mysqli->query("INSERT INTO reminder_log (student_id, professor_id, sent_at) VALUES ($student_id, $prof_id, NOW())");
        
        return true;

    } catch (Exception $e) {
        error_log("Failed to send email to student $student_id: " . $e->getMessage());
        return false;
    }
}

/* ---------- SEND REMINDER (MANUAL) ---------- */
if (isset($_POST['remind']) && isset($_POST['student_id'])) {
    header('Content-Type: application/json');
    $sid = (int)$_POST['student_id'];
    $module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : 0;

    // Count number of absences for this student in this module
    $stat = $mysqli->query("
        SELECT COUNT(*) as absence_count, u.email, u.nom, u.prenom
        FROM attendance a
        JOIN users u ON u.id = a.student_id
        WHERE a.student_id = $sid
          AND a.module_id = $module_id
          AND a.status = 'absent'
          AND a.module_id IN (SELECT id FROM modules WHERE professor_id = $prof_id)
    ")->fetch_assoc();

    if (!$stat) {
        echo json_encode(['ok'=>false,'reason'=>'No data']);
        exit();
    }

    // FIXED FORMULA: Each absence = 3.3%
    $absRate = $stat['absence_count'] * $absence_per_session;

    if ($absRate <= 0) {
        echo json_encode(['ok'=>false,'reason'=>'Below 20% threshold']);
        exit();
    }

    $today = date('Y-m-d');
    $already = $mysqli->query("SELECT 1 FROM reminder_log WHERE student_id=$sid AND professor_id=$prof_id AND DATE(sent_at)='$today'")->num_rows;
    if ($already) {
        echo json_encode(['ok'=>false,'reason'=>'Already reminded today']);
        exit();
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'farouk.zemoo@gmail.com';
        $mail->Password = 'kibh ehzs ofxg zpem';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty System');
        $mail->addAddress($stat['email'], $stat['prenom'].' '.$stat['nom']);
        $mail->isHTML(true);
        $mail->Subject = 'Attendance Reminder';
        $mail->Body = '
        <div style="font-family:Inter,sans-serif;max-width:600px;margin:auto;background:#0a0e27;color:#f0f4f8;border-radius:12px;overflow:hidden">
          <div style="padding:30px;">
            <h2 style="color:#00f5ff;margin-top:0;">Attendance Alert</h2>
            <p>Hi <strong>'.htmlspecialchars($stat['prenom']).'</strong>,</p>
            <p>Your absence rate is <strong>'.round($absRate,1).'%</strong> ('.$stat['absence_count'].' absences) in this module taught by Prof. '.htmlspecialchars($prof_info['prenom'].' '.$prof_info['nom']).'.</p>
            <p>Please attend upcoming classes to avoid academic penalties.</p>
          </div>
        </div>';

        $mail->send();
        $mysqli->query("INSERT INTO reminder_log (student_id, professor_id, sent_at) VALUES ($sid, $prof_id, NOW())");

        echo json_encode(['ok'=>true,'sent'=>true]);

    } catch (Exception $e) {
        echo json_encode(['ok'=>false,'reason'=>'Mail error','msg'=>$mail->ErrorInfo]);
    }
    exit();
}

/* ---------- FETCH CLASSES ---------- */
$classes = $mysqli->query("
    SELECT DISTINCT c.id, c.class_name
    FROM classes c
    INNER JOIN module_classes mc ON mc.class_id = c.id
    INNER JOIN modules m ON m.id = mc.module_id
    WHERE m.professor_id = $prof_id
    ORDER BY c.class_name
");

$selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

/* ---------- FETCH MODULES (if class selected) ---------- */
$modules = false;
if ($selected_class > 0) {
    $modules = $mysqli->query("
        SELECT DISTINCT m.id, m.module_name
        FROM modules m
        INNER JOIN module_classes mc ON mc.module_id = m.id
        WHERE m.professor_id = $prof_id AND mc.class_id = $selected_class
        ORDER BY m.module_name
    ");
}

$selected_module = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

/* ---------- FETCH STUDENTS (if both selected) ---------- */
$students = false;
$auto_sent_count = 0;
if ($selected_class > 0 && $selected_module > 0) {
    // Get students with absence count for this specific module
    $students = $mysqli->query("
        SELECT u.id, u.nom, u.prenom, u.email,
               COALESCE(SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END), 0) as absence_count
        FROM users u
        INNER JOIN student_classes sc ON sc.student_id = u.id
        LEFT JOIN attendance a ON a.student_id = u.id 
            AND a.module_id = $selected_module 
            AND a.status = 'absent'
        WHERE u.role = 'student' 
          AND sc.class_id = $selected_class
        GROUP BY u.id
        ORDER BY u.nom, u.prenom
    ");
    
    // Store students data for auto-sending and processing
    $students_data = [];
    if ($students) {
        while ($student = $students->fetch_assoc()) {
            $students_data[] = $student;
        }
        // Reset the result pointer for the HTML section
        $students->data_seek(0);
    }
    
    // Process automatic email sending for students with high absence rates
    if (!empty($students_data)) {
        foreach ($students_data as $student) {
            $absence_count = (int)$student['absence_count'];
            $absRate = $absence_count * $absence_per_session;
            
            // Auto-send email if absence rate >= 20%
            if ($absRate > 0) {
                $sent = autoSendReminder($student['id'], $selected_module, $prof_id, $prof_info, $mysqli, $absence_per_session);
                if ($sent) {
                    $auto_sent_count++;
                }
            }
        }
        // Reset the result pointer again after processing
        if ($students) {
            $students->data_seek(0);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Students | macademia Faculty</title>
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
    --text-primary: #f0f4f8; --text-secondary: #cbd5e1; --text-muted: #94a3b8;
    --error: #ff3b3b; --success: #00e676; --warning: #ffaa00;
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

/* === NAVBAR === */
.navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 70px;
    background: var(--bg-panel); backdrop-filter: var(--glass-blur);
    border-bottom: 1px solid var(--bg-card-border); display: flex;
    align-items: center; padding: 0 2rem; justify-content: space-between;
}
.navbar-left { display: flex; align-items: center; gap: 1rem; }
.sidebar-toggle {
    background: none; border: none; color: var(--primary); font-size: 1.5rem;
    cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: var(--transition);
}
.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.05); }

.logo {
    display: flex; align-items: center; gap: 0.75rem;
    text-decoration: none; color: var(--text-primary);
}
.logo-icon {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
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

/* Notification Dropdown */
.notification-wrapper { position: relative; }
.notification-bell {
    background: none; border: none; color: var(--text-secondary);
    font-size: 1.25rem; cursor: pointer; padding: 0.5rem;
    border-radius: 50%; transition: var(--transition); position: relative;
}
.notification-bell:hover { background: rgba(255, 255, 255, 0.05); color: var(--primary); }
.notification-dropdown {
    position: absolute; top: 100%; right: 0; margin-top: 1rem;
    background: #0a0e27; border: 1px solid var(--bg-card-border);
    border-radius: 14px; min-width: 320px; box-shadow: var(--shadow);
    opacity: 0; visibility: hidden; transform: translateY(-10px);
    transition: var(--transition); z-index: 1001;
}
.notification-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem; border-bottom: 1px solid var(--bg-card-border);
    color: var(--text-primary); font-weight: 600;
}
.notification-item {
    padding: 1rem; border-bottom: 1px solid var(--bg-card-border);
    display: flex; justify-content: space-between; align-items: center;
    transition: var(--transition);
}
.notification-item:hover { background: rgba(255, 255, 255, 0.03); }
.dropdown-empty {
    padding: 1rem; text-align: center; color: var(--text-muted);
}
.user-menu {
    display: flex; align-items: center; gap: 1rem;
    color: var(--text-secondary); font-weight: 600;
}
.user-avatar {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg, var(--secondary), var(--accent));
    display: grid; place-items: center; font-size: 1rem; font-weight: 700;
}

/* === MAIN LAYOUT === */
.dashboard-wrapper {
    display: flex; min-height: 100vh; margin-top: 70px;
}

/* === SIDEBAR === */
.sidebar {
    width: 280px; background: var(--bg-panel);
    border-right: 1px solid var(--bg-card-border); padding: 1.5rem 0;
    position: fixed; left: 0; top: 70px;
    height: calc(100vh - 70px); overflow-y: auto;
    transition: var(--transition); z-index: 999;
}
.sidebar.collapsed { transform: translateX(-100%); }
.sidebar-menu { list-style: none; }
.sidebar-link {
    display: flex; align-items: center; gap: 1rem;
    padding: 1rem 1.5rem; color: var(--text-secondary);
    text-decoration: none; font-weight: 500;
    border-radius: 0 12px 12px 0; transition: var(--transition);
}
.sidebar-link.active {
    color: var(--primary); background: rgba(255, 255, 255, 0.04);
}
.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.02); color: var(--text-primary);
}
.sidebar-link i { font-size: 1.25rem; width: 24px; text-align: center; }

/* === MAIN CONTENT === */
.main-content {
    flex: 1; margin-left: 280px; padding: 2rem;
    transition: var(--transition);
}
.sidebar.collapsed ~ .main-content { margin-left: 0; }

/* === PAGE SPECIFIC STYLES === */
.page-header { margin-bottom: 2.5rem; }
.page-title {
    font-size: 2.5rem; font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    margin-bottom: 0.5rem;
}
.page-subtitle { color: var(--text-secondary); font-size: 1.1rem; }

.auto-sent-notification {
    background: rgba(0, 230, 118, 0.1);
    border: 1px solid var(--success);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    color: var(--success);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.selector-container {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 1.5rem; margin-bottom: 2rem;
}
@media (max-width: 768px) {
    .selector-container { grid-template-columns: 1fr; }
}
.class-selector, .module-selector {
    padding: 1.5rem; background: var(--bg-card);
    border: 1px solid var(--bg-card-border); border-radius: 14px;
}
.class-selector select, .module-selector select {
    width: 100%; padding: 0.85rem 1rem;
    border: 2px solid var(--bg-card-border); border-radius: 10px;
    background: #12172f; color: var(--text-primary);
    font-size: 1rem; transition: var(--transition);
}
.class-selector select:focus, .module-selector select:focus {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(0, 245, 255, 0.1);
}
.class-selector label, .module-selector label {
    display: flex; align-items: center; gap: 0.5rem;
    margin-bottom: 0.75rem; font-weight: 600;
    color: var(--text-primary);
}
.module-selector.disabled {
    opacity: 0.5; pointer-events: none;
}

.toggle-container {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 2rem; padding: 1rem;
    background: var(--bg-card); border-radius: 12px;
    border: 1px solid var(--bg-card-border);
}
.toggle-switch {
    position: relative; width: 60px; height: 30px;
    background: var(--bg-card-border); border-radius: 15px;
    cursor: pointer; transition: var(--transition);
}
.toggle-switch.active { background: var(--error); }
.toggle-knob {
    position: absolute; top: 3px; left: 3px;
    width: 24px; height: 24px; background: white;
    border-radius: 50%; transition: var(--transition);
}
.toggle-switch.active .toggle-knob { transform: translateX(30px); }

.students-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
}
.student-card {
    background: var(--bg-card); backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border); border-radius: 20px;
    padding: 2rem; transition: var(--transition);
}
.student-card:hover {
    transform: translateY(-5px); box-shadow: var(--shadow);
    border-color: var(--primary);
}
.student-card.critical-alert {
    border-color: var(--error); background: rgba(255, 59, 59, 0.05);
}
.student-card.auto-emailed {
    border-color: var(--success); background: rgba(0, 230, 118, 0.05);
}
.student-header {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.5rem;
}
.student-avatar {
    width: 50px; height: 50px; border-radius: 50%;
    background: linear-gradient(135deg, var(--secondary), var(--accent));
    display: grid; place-items: center; font-size: 1.2rem; font-weight: 700;
}
.student-info h3 { color: var(--text-primary); margin-bottom: 0.25rem; }
.student-email { color: var(--text-muted); font-size: 0.9rem; }

.module-count {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(0, 245, 255, 0.1); padding: 0.5rem 1rem;
    border-radius: 50px; font-size: 0.9rem; color: var(--primary);
    margin-bottom: 1rem;
}
.attendance-display {
    text-align: center; padding: 1.5rem;
    background: rgba(255, 255, 255, 0.02); border-radius: 12px;
    margin-bottom: 1rem;
}
.rate-circle {
    position: relative; width: 100px; height: 100px;
    margin: 0 auto 1rem;
}
.rate-circle svg { transform: rotate(-90deg); }
.rate-circle-bg {
    fill: none; stroke: rgba(255, 255, 255, 0.1); stroke-width: 8;
}
.rate-circle-progress {
    fill: none; stroke-width: 8; stroke-linecap: round;
    transition: stroke-dashoffset 0.5s ease;
}
.rate-circle-progress.absence { stroke: var(--success); }
.rate-circle-progress.presence { stroke: var(--primary); }
.rate-percentage {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%); font-size: 1.5rem;
    font-weight: 800;
}
.rate-percentage.high { color: var(--success); }
.rate-percentage.medium { color: var(--warning); }
.rate-percentage.low { color: var(--error); }
.rate-percentage.na {
    font-size: 1rem; color: var(--text-muted);
}
.rate-label { color: var(--text-muted); font-size: 0.9rem; }

.alert-banner {
    display: none; padding: 0.75rem 1rem; border-radius: 8px;
    margin-top: 1rem; align-items: center; gap: 0.5rem;
    font-size: 0.9rem;
}
.alert-banner.critical {
    background: rgba(255, 59, 59, 0.1); border: 1px solid var(--error);
    color: var(--error);
}
.auto-sent-badge {
    background: rgba(0, 230, 118, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-left: 0.5rem;
}

.student-actions {
    display: flex; gap: 0.75rem;
}
.btn-action {
    flex: 1; padding: 0.75rem 1rem; border-radius: 10px;
    font-weight: 600; cursor: pointer; transition: var(--transition);
    text-decoration: none; text-align: center; font-size: 0.9rem;
    border: none;
}
.btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: var(--bg-main);
}
.btn-primary:hover {
    transform: translateY(-2px); box-shadow: 0 5px 15px var(--primary-glow);
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.05); color: var(--text-secondary);
    border: 1px solid var(--bg-card-border);
}
.btn-secondary:hover {
    background: rgba(0, 245, 255, 0.1); color: var(--primary);
}
.btn-secondary:disabled {
    opacity: 0.5; cursor: not-allowed;
}
.empty-state {
    text-align: center; padding: 4rem 2rem; max-width: 600px;
    margin: 0 auto;
}
.empty-state i { font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem; }
.empty-state h3 { color: var(--text-primary); margin-bottom: 0.5rem; }

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
    .students-grid { grid-template-columns: 1fr; padding: 0; }
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
        <a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/profdash" class="logo">
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
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/profdash" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/profdash/my_modules" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/profdash/students" class="sidebar-link active"><i class="bi bi-people"></i><span>Students</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/profdash/reports" class="sidebar-link"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
            <li><a href="<?php echo defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost'; ?>/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">My Students</h1>
            <p class="page-subtitle">Monitor attendance by class and module</p>
        </div>

        <!-- AUTO-SENT NOTIFICATION -->
        <?php if ($auto_sent_count > 0): ?>
        <div class="auto-sent-notification">
            <i class="bi bi-check-circle-fill"></i>
            <span>Automatically sent <?php echo $auto_sent_count; ?> attendance reminder email(s) to students with ≥20% absence rate.</span>
        </div>
        <?php endif; ?>

        <!-- CLASS AND MODULE SELECTORS -->
        <div class="selector-container">
            <div class="class-selector">
                <label for="classSelect"><i class="bi bi-collection"></i> Select Class:</label>
                <select id="classSelect" onchange="classSelected(this.value)">
                    <option value="">-- Choose a Class --</option>
                    <?php while($c = $classes->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>" <?= $selected_class == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['class_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="module-selector <?= $selected_class ? '' : 'disabled' ?>">
                <label for="moduleSelect"><i class="bi bi-bookmark"></i> Select Module:</label>
                <select id="moduleSelect" onchange="moduleSelected(this.value)" <?= $selected_class ? '' : 'disabled' ?>>
                    <option value="">-- Choose Module --</option>
                    <?php if($modules): while($m = $modules->fetch_assoc()): ?>
                        <option value="<?= $m['id'] ?>" <?= $selected_module == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['module_name']) ?>
                        </option>
                    <?php endwhile; endif; ?>
                </select>
            </div>
        </div>

        <!-- STUDENTS SECTION -->
        <?php if($selected_class > 0 && $selected_module > 0): ?>
            <div class="toggle-container">
                <span style="color:var(--error);font-weight:600;">Absence Rate</span>
                <div class="toggle-switch active" id="displayToggle"><div class="toggle-knob"></div></div>
                <span style="color:var(--text-secondary);">Presence Rate</span>
            </div>

            <div class="students-grid">
            <?php if($students && $students->num_rows > 0): ?>
                <?php while($s = $students->fetch_assoc()):
                    $absence_count = (int)$s['absence_count'];
                    
                    // Each absence = 3.3%
                    $absRate = $absence_count * $absence_per_session;
                    $presRate = 100 - $absRate;
                    $critical = $absRate >= 20;
                    
                    // Check if auto-sent today
                    $today = date('Y-m-d');
                    $auto_sent = false;
                    if ($critical) {
                        $auto_check = $mysqli->query("SELECT 1 FROM reminder_log WHERE student_id={$s['id']} AND professor_id=$prof_id AND DATE(sent_at)='$today'");
                        $auto_sent = $auto_check->num_rows > 0;
                    }
                    
                    // Cap at 100%
                    $absRate = min($absRate, 100);
                    $presRate = max($presRate, 0);
                ?>
                <div class="student-card <?= $critical ? 'critical-alert' : '' ?> <?= $auto_sent ? 'auto-emailed' : '' ?>" 
                     data-rates='{"absence":<?= $absRate ?>,"presence":<?= $presRate ?>,"absence_count":<?= $absence_count ?>}' 
                     data-id="<?= $s['id'] ?>">
                    <div class="student-header">
                        <div class="student-avatar"><?= substr($s['prenom'],0,1) ?></div>
                        <div class="student-info">
                            <h3><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?><?php if ($auto_sent): ?><span class="auto-sent-badge">Auto Sent</span><?php endif; ?></h3>
                            <div class="student-email"><?= htmlspecialchars($s['email']) ?></div>
                        </div>
                    </div>
                    <div class="module-count"><i class="bi bi-calendar-x"></i> <?= $absence_count ?> absence(s) × 3.3%</div>
                    <div class="attendance-display">
                        <div class="rate-circle">
                            <svg width="100" height="100">
                                <circle class="rate-circle-bg" cx="50" cy="50" r="42"></circle>
                                <circle class="rate-circle-progress absence" cx="50" cy="50" r="42" stroke-dasharray="263.89" stroke-dashoffset="<?= 263.89-($absRate/100*263.89) ?>"></circle>
                            </svg>
                            <div class="rate-percentage <?= $absRate>=20?'low':($absRate>=15?'medium':'high') ?>" id="rate-<?= $s['id'] ?>">
                                <?= round($absRate, 1) ?>
                            </div>
                        </div>
                        <div class="rate-label" id="label-<?= $s['id'] ?>">Absence Rate</div>
                        <div class="alert-banner critical" id="alert-<?= $s['id'] ?>" style="<?= $critical?'display:flex':'display:none' ?>">
                            <i class="bi bi-exclamation-triangle-fill"></i><span>20%+ absence detected</span>
                        </div>
                    </div>
                    <div class="student-actions">
                        <a href="student_detail.php?id=<?= $s['id'] ?>&class_id=<?= $selected_class ?>&module_id=<?= $selected_module ?>" class="btn-action btn-primary"><i class="bi bi-eye"></i> View Details</a>
                        <button class="btn-action btn-secondary" onclick="sendReminder(<?= $s['id'] ?>, event)" <?= $auto_sent ? 'disabled' : '' ?>>
                            <i class="bi bi-envelope"></i> <?= $auto_sent ? 'Already Sent' : 'Send Reminder' ?>
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h3>No Students Enrolled</h3>
                    <p>This class has no students enrolled in the selected module.</p>
                </div>
            <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-collection"></i>
                <h3>Select Class & Module</h3>
                <p>Please select a class and module from the dropdowns above to view students and their attendance.</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
function classSelected(classId) {
    if (classId) {
        window.location.href = '?class_id=' + classId;
    } else {
        window.location.href = '?';
    }
}

function moduleSelected(moduleId) {
    const classId = <?= $selected_class ?>;
    if (moduleId) {
        window.location.href = '?class_id=' + classId + '&module_id=' + moduleId;
    } else {
        window.location.href = '?class_id=' + classId;
    }
}

const displayToggle = document.getElementById('displayToggle');
let absenceMode = true;

displayToggle.addEventListener('click', () => {
    absenceMode = !absenceMode;
    displayToggle.classList.toggle('active');
    
    document.querySelectorAll('.student-card').forEach(card => {
        const data = JSON.parse(card.dataset.rates);
        const id = card.dataset.id;
        const rateEl = document.getElementById('rate-' + id);
        const labelEl = document.getElementById('label-' + id);
        const progEl = card.querySelector('.rate-circle-progress');
        const alertEl = document.getElementById('alert-' + id);
        
        if (absenceMode) {
            // Absence mode
            rateEl.textContent = Math.round(data.absence * 10) / 10 + '%';
            rateEl.className = 'rate-percentage ' + (data.absence >= 20 ? 'low' : (data.absence >= 15 ? 'medium' : 'high'));
            labelEl.textContent = 'Absence Rate';
            progEl.style.strokeDashoffset = 263.89 - (263.89 * data.absence / 100);
            progEl.className = 'rate-circle-progress absence';
            if (alertEl) alertEl.style.display = data.absence >= 20 ? 'flex' : 'none';
        } else {
            // Presence mode
            rateEl.textContent = Math.round(data.presence * 10) / 10 + '%';
            rateEl.className = 'rate-percentage ' + (data.presence >= 80 ? 'high' : (data.presence >= 50 ? 'medium' : 'low'));
            labelEl.textContent = 'Presence Rate';
            progEl.style.strokeDashoffset = 263.89 - (263.89 * data.presence / 100);
            progEl.className = 'rate-circle-progress presence';
            if (alertEl) alertEl.style.display = 'none';
        }
    });
});

function sendReminder(studentId, event) {
    const btn = event.target.closest('button');
    const orig = btn.innerHTML;
    const moduleId = <?= $selected_module ?>;
    btn.disabled = true;
    
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `remind=1&student_id=${studentId}&module_id=${moduleId}`
    })
    .then(r => r.json())
    .then(res => {
        if(res.ok && res.sent) {
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Sent';
            btn.style.background = 'linear-gradient(135deg,#00e676,#00b200)';
            btn.style.color = 'white';
        } else {
            alert('Failed: ' + res.reason + (res.msg ? '\n' + res.msg : ''));
            btn.disabled = false;
            btn.innerHTML = orig;
        }
    })
    .catch(err => {
        alert('AJAX Error: ' + err);
        btn.disabled = false;
        btn.innerHTML = orig;
    });
}

// === SIDEBAR & NOTIFICATION SCRIPTS ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
sidebarToggle.addEventListener('click', () => { sidebar.classList.toggle('collapsed'); });

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