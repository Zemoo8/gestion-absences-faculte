<?php
session_start();
require_once '../login/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login/login.php");
    exit();
}

$prof_id = $_SESSION['user_id'];

// Get module list for filter
$modules = $mysqli->query("SELECT id, module_name FROM modules WHERE professor_id = $prof_id ORDER BY module_name");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Reports | macademia Faculty</title>
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
.report-card {
    background: var(--bg-card);
    border: 1px solid var(--bg-card-border);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
}
.download-btn {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    margin-top: 1rem;
}
</style>
</head>
<body>
<h1 style="margin-bottom: 2rem;">Attendance Reports</h1>

<div class="report-card">
    <h2 style="margin-bottom: 1rem;">Export Module Report</h2>
    <p style="color: #94a3b8; margin-bottom: 1rem;">Generate PDF report for selected module</p>
    
    <select id="moduleSelect" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255,255,255,0.05); border: 1px solid var(--bg-card-border); color: var(--text-primary);">
        <option value="">Select Module</option>
        <?php while ($mod = $modules->fetch_assoc()): ?>
        <option value="<?php echo $mod['id']; ?>"><?php echo htmlspecialchars($mod['module_name']); ?></option>
        <?php endwhile; ?>
    </select>
    
    <button class="download-btn" onclick="alert('PDF generation coming soon');">
        <i class="bi bi-download"></i> Download PDF
    </button>
</div>

<div class="report-card">
    <h2 style="margin-bottom: 1rem;">At-Risk Students Report</h2>
    <p style="color: #94a3b8; margin-bottom: 1rem;">List students with >20% absence rate</p>
    <button class="download-btn" onclick="location.href='students.php'">
        <i class="bi bi-exclamation-triangle"></i> View At-Risk Students
    </button>
</div>
</body>
</html>