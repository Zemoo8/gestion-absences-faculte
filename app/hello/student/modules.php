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
$my_modules = $mysqli->query("
	SELECT m.module_name, u.prenom as prof_prenom, u.nom as prof_nom, ms.weekday, ms.start_time
	FROM modules m
	JOIN users u ON m.professor_id = u.id
	LEFT JOIN module_schedule ms ON m.id = ms.module_id
	WHERE m.id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)
");
$module_count = $my_modules ? $my_modules->num_rows : 0;
$days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Modules | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%); color: #f0f4f8; min-height: 100vh; }
.main-content { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 80vh; width: 100%; }
.hero-header { width:100%;max-width:900px;margin:3rem auto 2rem auto;padding:2.5rem 2rem;background:linear-gradient(135deg,#00f5ff22 0%,#7b2ff722 100%);border-radius:24px;box-shadow:0 8px 32px rgba(0,0,0,0.18);display:flex;align-items:center;gap:2rem; }
.hero-icon { width:80px;height:80px;background:linear-gradient(135deg,#00f5ff,#7b2ff7);border-radius:50%;display:grid;place-items:center;font-size:2.5rem;box-shadow:0 0 30px #00f5ff44; }
.hero-content h1 { font-size:2.3rem;font-weight:900;margin:0;color:#00f5ff;letter-spacing:-1px; }
.hero-content p { color:#cbd5e1;font-size:1.15rem;margin-top:0.5rem; }
.sidebar { position:fixed;left:0;top:0;height:100vh;width:220px;background:rgba(10,14,39,0.95);box-shadow:0 0 40px 0 rgba(0,0,0,0.25);z-index:100;display:flex;flex-direction:column; }
.sidebar-menu { list-style:none;padding:0;margin:0;width:100%; }
.sidebar-link { display:flex;align-items:center;gap:10px;padding:1rem 1.5rem;color:#cbd5e1;text-decoration:none;font-weight:500;border-radius:0 12px 12px 0;transition:all 0.3s; }
.sidebar-link.active { color:#00f5ff;background:rgba(255,255,255,0.04); }
.sidebar-link:hover { background:rgba(255,255,255,0.02); }
.sidebar-link i { font-size:1.25rem;width:24px;text-align:center; }
.main-content { margin-left:220px;padding:2rem; }
.modules-grid { width:100%;max-width:900px;display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:2rem;margin-top:2rem; }
.module-card { background:rgba(0,245,255,0.08);border-radius:18px;padding:2rem 1.5rem;box-shadow:0 2px 12px rgba(0,0,0,0.10);display:flex;flex-direction:column;gap:1rem;align-items:flex-start;transition:transform 0.2s; }
.module-card:hover { transform:translateY(-6px) scale(1.03);box-shadow:0 8px 32px #00f5ff33; }
.module-title { font-size:1.35rem;font-weight:800;color:#00f5ff;margin-bottom:0.2rem; }
.module-prof { font-size:1.05rem;color:#cbd5e1; }
.module-sched { font-size:0.98rem;color:#94a3b8;margin-top:0.2rem; }
.module-badge { display:inline-block;background:linear-gradient(135deg,#7b2ff7,#00f5ff);color:white;font-size:0.85rem;font-weight:700;padding:0.3rem 0.9rem;border-radius:12px;margin-bottom:0.7rem;letter-spacing:0.5px;box-shadow:0 2px 8px #00f5ff22; }
.modules-stats { width:100%;max-width:900px;display:flex;gap:2rem;justify-content:space-between;margin:2.5rem auto 0 auto; }
.stat-card { flex:1;background:rgba(255,255,255,0.04);border-radius:18px;padding:1.5rem 1.2rem;box-shadow:0 2px 12px rgba(0,0,0,0.10);display:flex;flex-direction:column;align-items:center; }
.stat-label { color:#cbd5e1;font-size:1.05rem;margin-bottom:0.5rem; }
.stat-value { font-size:2.1rem;font-weight:900;color:#00f5ff; }
</style>
</head>
<body>
	<aside class="sidebar" id="sidebar">
		<ul class="sidebar-menu">
			<li><a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link active"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
			<li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
		</ul>
	</aside>
	<main class="main-content">
		<div class="hero-header">
			<div class="hero-icon"><i class="bi bi-bookshelf"></i></div>
			<div class="hero-content">
				<h1>My Modules</h1>
				<p>Explore your enrolled modules, meet your professors, and see your weekly schedule at a glance. Stay on top of your academic journey!</p>
			</div>
		</div>
		<div class="modules-stats">
			<div class="stat-card">
				<div class="stat-label">Total Modules</div>
				<div class="stat-value"><?php echo $module_count; ?></div>
			</div>
			<div class="stat-card">
				<div class="stat-label">Unique Professors</div>
				<div class="stat-value">
				<?php
				$profs = $mysqli->query("SELECT COUNT(DISTINCT professor_id) as profs FROM modules WHERE id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)");
				echo $profs && ($row = $profs->fetch_assoc()) ? $row['profs'] : 0;
				?>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-label">Earliest Class</div>
				<div class="stat-value">
				<?php
				$earliest = $mysqli->query("SELECT MIN(start_time) as min_time FROM module_schedule WHERE module_id IN (SELECT DISTINCT module_id FROM attendance WHERE student_id = $student_id)");
				echo $earliest && ($row = $earliest->fetch_assoc()) && $row['min_time'] ? substr($row['min_time'],0,5) : '--:--';
				?>
				</div>
			</div>
		</div>
		<div class="modules-grid">
			<?php if($my_modules && $my_modules->num_rows > 0): ?>
				<?php while($mod = $my_modules->fetch_assoc()): ?>
				<div class="module-card">
					<span class="module-badge"><i class="bi bi-mortarboard"></i> Module</span>
					<div class="module-title"><?php echo htmlspecialchars($mod['module_name']); ?></div>
					<div class="module-prof">Professor: <?php echo htmlspecialchars($mod['prof_prenom'] . ' ' . $mod['prof_nom']); ?></div>
					<?php if(!empty($mod['weekday']) && !empty($mod['start_time'])): ?>
						<div class="module-sched"><i class="bi bi-calendar-event"></i> <?php echo $days[(int)$mod['weekday']] ?? $mod['weekday']; ?>, <?php echo $mod['start_time']; ?></div>
					<?php endif; ?>
				</div>
				<?php endwhile; ?>
			<?php else: ?>
				<div class="module-card" style="opacity:0.7;text-align:center;">
					<span class="module-badge"><i class="bi bi-mortarboard"></i> Module</span>
					<div class="module-title">No modules enrolled</div>
					<div class="module-prof">Check with your faculty for enrollment details.</div>
				</div>
			<?php endif; ?>
		</div>
	</main>
</body>
</html>
