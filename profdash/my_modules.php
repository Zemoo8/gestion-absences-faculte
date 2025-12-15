<?php
session_start();
require_once '../login/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];

// Get all professor modules with stats
$modules = $mysqli->query("
    SELECT m.id, m.module_name, m.total_hours,
           COUNT(DISTINCT a.student_id) as enrolled,
           AVG(CASE WHEN a.status = 'present' THEN 100 ELSE 0 END) as avg_attendance
    FROM modules m
    LEFT JOIN attendance a ON m.id = a.module_id
    WHERE m.professor_id = $prof_id
    GROUP BY m.id
    ORDER BY m.module_name
");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Modules | macademia Faculty</title>
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
.module-list {
    display: grid;
    gap: 1rem;
}
.module-item {
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.stat-box {
    text-align: center;
    padding: 0.5rem 1rem;
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
}
.stat-box .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}
</style>
</head>
<body>
<h1 style="margin-bottom: 2rem;">My Modules</h1>

<div class="module-list">
    <?php while ($mod = $modules->fetch_assoc()): ?>
    <div class="module-item">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($mod['module_name']); ?></h2>
            <p style="color: #94a3b8; font-size: 0.875rem;"><?php echo $mod['total_hours']; ?> hours planned</p>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <div class="stat-box">
                <div class="value"><?php echo $mod['enrolled'] ?? 0; ?></div>
                <div style="font-size: 0.75rem;">Students</div>
            </div>
            <div class="stat-box">
                <div class="value"><?php echo round($mod['avg_attendance'] ?? 0); ?>%</div>
                <div style="font-size: 0.75rem;">Avg Attendance</div>
            </div>
            <button onclick="location.href='take_attendance.php?module=<?php echo $mod['id']; ?>'" 
                    style="background: var(--primary); border: none; padding: 0.5rem 1rem; border-radius: 8px; color: black; font-weight: 600; cursor: pointer;">
                Take Attendance
            </button>
        </div>
    </div>
    <?php endwhile; ?>
</div>
</body>
</html>