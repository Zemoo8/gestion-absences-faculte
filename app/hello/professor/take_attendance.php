<?php
// Bootstrap loads config and starts session; view remains presentation-only.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// Redirect direct access to canonical front-controller take_attendance route
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
    $module = isset($_GET['module']) ? (int)$_GET['module'] : 0;
    $base = defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $target = $base . '/index.php/profdash/take_attendance' . ($module ? '?module=' . $module : '');
    header('Location: ' . $target);
    exit();
}

function ip_in_subnet($ip, $subnet, $mask) {
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = ip2long($mask);
    return ($ip & $mask) === ($subnet & $mask);
}

$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

// Allow localhost for testing (IPv4 and IPv6)
if (!in_array($client_ip, ['127.0.0.1', '::1']) 
    && !ip_in_subnet($client_ip, '192.168.50.0', '255.255.255.0')) {
    die("<script>alert('Access denied: you must be on the allowed network'); window.location.href='../login/login.php';</script>");
}



if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

$prof_id   = $_SESSION['user_id'];
$module_id = isset($_GET['module']) ? (int)$_GET['module'] : 0;

/* =======================
   VERIFY MODULE OWNERSHIP
======================= */
$check = $mysqli->prepare("
    SELECT id, module_name 
    FROM modules 
    WHERE id = ? AND professor_id = ?
");
$check->bind_param("ii", $module_id, $prof_id);
$check->execute();
$module = $check->get_result()->fetch_assoc();

if (!$module) {
    die("Unauthorized module access.");
}

/* =======================
   GET CURRENT SESSION
======================= */
date_default_timezone_set('Africa/Tunis');
$current_day  = date('w');
$current_time = date('H:i:s');

$sessionStmt = $mysqli->prepare("
    SELECT start_time, end_time
    FROM module_schedule
    WHERE module_id = ?
      AND weekday = ?
      AND start_time <= ?
      AND end_time >= ?
    LIMIT 1
");
$sessionStmt->bind_param("isss", $module_id, $current_day, $current_time, $current_time);
$sessionStmt->execute();
$currentSession = $sessionStmt->get_result()->fetch_assoc();

if (!$currentSession) {
    die("<script>alert('Attendance can only be taken during scheduled class hours'); window.location.href='prof_dashboard.php';</script>");
}

$start_time = $currentSession['start_time'];
$end_time   = $currentSession['end_time'];

/* =======================
   LOAD STUDENTS + SESSION STATUS
======================= */
$students = $mysqli->query("
    SELECT u.id, u.nom, u.prenom,
           a.status
    FROM users u
    LEFT JOIN attendance a
      ON a.student_id = u.id
     AND a.module_id  = $module_id
     AND a.date       = CURDATE()
     AND a.start_time = '$start_time'
     AND a.end_time   = '$end_time'
    WHERE u.role = 'student'
    ORDER BY u.nom
");

/* =======================
   SAVE ATTENDANCE
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mysqli->begin_transaction();

    try {
        foreach ($_POST['status'] as $student_id => $status) {

            $del = $mysqli->prepare("
                DELETE FROM attendance
                WHERE student_id = ?
                  AND module_id  = ?
                  AND date       = CURDATE()
                  AND start_time = ?
                  AND end_time   = ?
            ");
            $del->bind_param("iiss", $student_id, $module_id, $start_time, $end_time);
            $del->execute();

            $ins = $mysqli->prepare("
                INSERT INTO attendance
                (student_id, module_id, date, start_time, end_time, status)
                VALUES (?, ?, CURDATE(), ?, ?, ?)
            ");
            $ins->bind_param("iisss", $student_id, $module_id, $start_time, $end_time, $status);
            $ins->execute();
        }

        $mysqli->commit();
        header("Location: " . PUBLIC_URL . "/index.php/profdash/prof_dashboard?msg=Attendance saved");
        exit();

    } catch (Exception $e) {
        $mysqli->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Take Attendance | <?= htmlspecialchars($module['module_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
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
.attendance-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}
.student-card {
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 12px;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.present { border-color: #00e676; }
.absent  { border-color: #ff3b3b; }

.attendance-toggle {
    width: 60px;
    height: 30px;
    border-radius: 15px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s;
}
.attendance-toggle.present { background: #00e676; }
.attendance-toggle.absent  { background: #ff3b3b; }

.attendance-toggle::after {
    content: '';
    position: absolute;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: white;
    top: 2px;
    left: 2px;
    transition: all 0.3s;
}
.attendance-toggle.absent::after {
    left: 32px;
}

.submit-btn {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    padding: 1rem 2rem;
    border-radius: 12px;
    color: white;
    font-weight: 700;
    cursor: pointer;
}
</style>
</head>
<body>

<h1><?= htmlspecialchars($module['module_name']) ?> – <?= date('M d, Y') ?></h1>

<form method="POST">
<div class="attendance-grid">
<?php while ($s = $students->fetch_assoc()):
    $status = $s['status'] ?? 'present';
?>
<div class="student-card <?= $status ?>">
    <div>
        <strong><?= htmlspecialchars($s['nom'].' '.$s['prenom']) ?></strong>
        <div style="font-size:.75rem;color:#94a3b8">ID: <?= $s['id'] ?></div>
    </div>

    <input type="hidden"
           name="status[<?= $s['id'] ?>]"
           value="<?= $status ?>"
           id="input-<?= $s['id'] ?>">

    <div class="attendance-toggle <?= $status ?>"
         onclick="toggleStatus(<?= $s['id'] ?>)">
    </div>
</div>
<?php endwhile; ?>
</div>

<button class="submit-btn">Save Attendance</button>
</form>

<script>
function toggleStatus(studentId) {
    const input  = document.getElementById('input-' + studentId);
    const card   = input.closest('.student-card');
    const toggle = card.querySelector('.attendance-toggle');

    if (input.value === 'present') {
        input.value = 'absent';

        card.classList.remove('present');
        card.classList.add('absent');

        toggle.classList.remove('present');
        toggle.classList.add('absent');
    } else {
        input.value = 'present';

        card.classList.remove('absent');
        card.classList.add('present');

        toggle.classList.remove('absent');
        toggle.classList.add('present');
    }
}
</script>

</body>
</html>
