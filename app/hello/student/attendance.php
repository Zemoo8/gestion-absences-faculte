<?php
if (!defined('BASE_PATH')) {
	require_once __DIR__ . '/../../../bootstrap.php';
}
global $mysqli;
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'student'){
	header("Location: " . BASE_URL . "/login/login");
	exit();
}
$student_id = $_SESSION['user_id'];
$stats_result = $mysqli->query("SELECT 
	(SELECT COUNT(DISTINCT a.module_id) FROM attendance a WHERE a.student_id = $student_id) as total_modules,
	(SELECT COUNT(*) FROM attendance WHERE student_id = $student_id AND status = 'absent') as total_absences,
	(SELECT COUNT(*) FROM attendance WHERE student_id = $student_id) as total_classes
");
if ($stats_result && ($row = $stats_result->fetch_assoc())) {
	$stats = $row;
} else {
	$stats = [
		'total_modules' => 0,
		'total_absences' => 0,
		'total_classes' => 0
	];
}
$attendance_rate = $stats['total_classes'] > 0 
	? round((($stats['total_classes'] - $stats['total_absences']) / $stats['total_classes']) * 100, 1) 
	: 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Attendance Summary | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%); color: #f0f4f8; min-height: 100vh; }
.main-content { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 80vh; width: 100%; }
.info-card { width:100%;max-width:600px;margin-top:3rem;box-shadow:0 30px 60px -12px rgba(0,0,0,0.85);background:rgba(255,255,255,0.04);border-radius:20px;padding:2.5rem 2.5rem;display:flex;flex-direction:column;align-items:center; }
.sidebar { position:fixed;left:0;top:0;height:100vh;width:220px;background:rgba(10,14,39,0.95);box-shadow:0 0 40px 0 rgba(0,0,0,0.25);z-index:100;display:flex;flex-direction:column; }
.sidebar-menu { list-style:none;padding:0;margin:0;width:100%; }
.sidebar-link { display:flex;align-items:center;gap:10px;padding:1rem 1.5rem;color:#cbd5e1;text-decoration:none;font-weight:500;border-radius:0 12px 12px 0;transition:all 0.3s; }
.sidebar-link.active { color:#00f5ff;background:rgba(255,255,255,0.04); }
.sidebar-link:hover { background:rgba(255,255,255,0.02); }
.sidebar-link i { font-size:1.25rem;width:24px;text-align:center; }
.main-content { margin-left:220px;padding:2rem; }
</style>
</head>
<body>
	<aside class="sidebar" id="sidebar">
		<ul class="sidebar-menu">
			<li><a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link active"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
			<li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
		</ul>
	</aside>
	<main class="main-content">
		<div class="info-card">
			<h3 style="font-size:2rem;font-weight:800;color:#00f5ff;margin-bottom:2rem;">Attendance Summary</h3>
			<div style="display:flex;flex-direction:column;align-items:center;gap:2rem;">
				<div style="position:relative;width:180px;height:180px;">
					<svg width="180" height="180">
						<circle cx="90" cy="90" r="80" stroke="#222b45" stroke-width="16" fill="none"/>
						<circle cx="90" cy="90" r="80" stroke="#00f5ff" stroke-width="16" fill="none" stroke-dasharray="502" stroke-dashoffset="<?php echo 502 - round($attendance_rate/100*502); ?>" style="transition:stroke-dashoffset 1s;"/>
					</svg>
					<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:2.5rem;font-weight:900;color:#00f5ff;">
						<?php echo $attendance_rate; ?>%
					</div>
				</div>
				<div style="display:flex;justify-content:space-between;width:100%;max-width:350px;">
					<div style="text-align:center;">
						<div style="font-size:1.1rem;color:#cbd5e1;">Total Classes</div>
						<div style="font-size:1.5rem;font-weight:700;color:#f0f4f8;margin-top:0.5rem;">
							<?php echo $stats['total_classes']; ?>
						</div>
					</div>
					<div style="text-align:center;">
						<div style="font-size:1.1rem;color:#cbd5e1;">Absences</div>
						<div style="font-size:1.5rem;font-weight:700;color:#ff3b3b;margin-top:0.5rem;">
							<?php echo $stats['total_absences']; ?>
						</div>
					</div>
					<div style="text-align:center;">
						<div style="font-size:1.1rem;color:#cbd5e1;">Enrolled Modules</div>
						<div style="font-size:1.5rem;font-weight:700;color:#00f5ff;margin-top:0.5rem;">
							<?php echo $stats['total_modules']; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>
</body>
</html>
