<?php
session_start();
require_once '../login/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];
$module_id = isset($_GET['module']) ? (int)$_GET['module'] : 0;

// Verify professor owns this module
$check = $mysqli->prepare("SELECT id, module_name FROM modules WHERE id = ? AND professor_id = ?");
$check->bind_param("ii", $module_id, $prof_id);
$check->execute();
$module = $check->get_result()->fetch_assoc();

if (!$module) {
    die("Invalid module access");
}

// Check time restriction
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
    return $stmt->get_result()->fetch_assoc()['count'] > 0;
}

if (!canTakeAttendance($module_id)) {
    die("<script>alert('Attendance can only be taken during scheduled class hours'); window.location.href='prof_dashboard.php';</script>");
}

// Get students enrolled in this module
$students = $mysqli->query("
    SELECT u.id, u.nom, u.prenom, u.email,
           a.status, a.date
    FROM users u
    LEFT JOIN attendance a ON u.id = a.student_id AND a.module_id = $module_id AND a.date = CURDATE()
    WHERE u.role = 'student'
    ORDER BY u.nom
");

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqli->begin_transaction();
    try {
        foreach ($_POST['status'] as $student_id => $status) {
            // Delete existing record for today
            $stmt = $mysqli->prepare("DELETE FROM attendance WHERE student_id = ? AND module_id = ? AND date = CURDATE()");
            $stmt->bind_param("ii", $student_id, $module_id);
            $stmt->execute();
            
            // Insert new record
            $stmt = $mysqli->prepare("INSERT INTO attendance (student_id, module_id, date, status) VALUES (?, ?, CURDATE(), ?)");
            $stmt->bind_param("iis", $student_id, $module_id, $status);
            $stmt->execute();
            
            // Check 20% threshold and send email
            if ($status === 'absent') {
                checkAndNotifyAbsence($student_id, $module_id);
            }
        }
        $mysqli->commit();
        header("Location: prof_dashboard.php?msg=Attendance saved");
    } catch (Exception $e) {
        $mysqli->rollback();
        die("Error: " . $e->getMessage());
    }
}

// Check and notify if absence > 20%
function checkAndNotifyAbsence($student_id, $module_id) {
    global $mysqli;
    $data = $mysqli->query("
        SELECT u.email, u.nom, u.prenom, m.module_name, m.total_hours,
               COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absences
        FROM users u
        JOIN modules m ON m.id = $module_id
        LEFT JOIN attendance a ON a.student_id = u.id AND a.module_id = m.id
        WHERE u.id = $student_id
        GROUP BY u.id, m.id
    ")->fetch_assoc();
    
    if ($data && $data['total_hours'] > 0) {
        $rate = ($data['absences'] / $data['total_hours']) * 100;
        if ($rate > 20) {
            sendAbsenceAlert($data['email'], $data['nom'], $data['prenom'], $data['module_name'], round($rate, 1));
        }
    }
}

function sendAbsenceAlert($student_email, $nom, $prenom, $module_name, $rate) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'farouk.zemoo@gmail.com';
        $mail->Password = 'kibh ehzs ofxg zpem';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty');
        $mail->addAddress($student_email);
        $mail->addBCC('farouk.zemoo@gmail.com'); // Admin copy
        
        $mail->isHTML(true);
        $mail->Subject = 'Absence Alert - ' . $module_name;
        $mail->Body = "
            <h2>Absence Warning</h2>
            <p>Dear $nom $prenom,</p>
            <p>Your absence rate in <strong>$module_name</strong> has reached <strong>$rate%</strong>.</p>
            <p>If you exceed 20%, you may be barred from the exam.</p>
            <p>Please contact your professor immediately.</p>
        ";
        
        $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Take Attendance | <?php echo htmlspecialchars($module['module_name']); ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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
.absent { border-color: #ff3b3b; }
.attendance-toggle {
    width: 60px;
    height: 30px;
    border-radius: 15px;
    position: relative;
    cursor: pointer;
    transition: all 0.3s;
}
.attendance-toggle.present {
    background: #00e676;
}
.attendance-toggle.absent {
    background: #ff3b3b;
}
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
<h1 style="margin-bottom: 2rem;"><?php echo htmlspecialchars($module['module_name']); ?> - <?php echo date('M d, Y'); ?></h1>

<form method="POST" id="attendanceForm">
    <div class="attendance-grid">
        <?php while ($student = $students->fetch_assoc()): ?>
        <?php $current_status = $student['status'] ?? 'present'; ?>
        <div class="student-card <?php echo $current_status; ?>">
            <div>
                <strong><?php echo htmlspecialchars($student['nom'] . ' ' . $student['prenom']); ?></strong>
                <div style="font-size: 0.75rem; color: #94a3b8;">ID: <?php echo $student['id']; ?></div>
            </div>
            
            <input type="hidden" name="status[<?php echo $student['id']; ?>]" value="<?php echo $current_status; ?>" id="input-<?php echo $student['id']; ?>">
            
            <div class="attendance-toggle <?php echo $current_status; ?>" 
                 onclick="toggleStatus(<?php echo $student['id']; ?>)"></div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <button type="submit" class="submit-btn">Save Attendance</button>
</form>

<script>
function toggleStatus(studentId) {
    const input = document.getElementById('input-' + studentId);
    const toggle = input.parentElement.querySelector('.attendance-toggle');
    
    if (input.value === 'present') {
        input.value = 'absent';
        toggle.classList.remove('present');
        toggle.classList.add('absent');
        input.parentElement.classList.remove('present');
        input.parentElement.classList.add('absent');
    } else {
        input.value = 'present';
        toggle.classList.remove('absent');
        toggle.classList.add('present');
        input.parentElement.classList.remove('absent');
        input.parentElement.classList.add('present');
    }
}
</script>
</body>
</html>