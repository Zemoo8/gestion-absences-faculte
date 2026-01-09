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
$absences = $mysqli->query("SELECT m.module_name, COUNT(*) as count FROM attendance a JOIN modules m ON a.module_id = m.id WHERE a.student_id = $student_id AND a.status = 'absent' GROUP BY m.module_name");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Absences | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%); color: #f0f4f8; min-height: 100vh; }
.main-content { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 80vh; width: 100%; }
.info-card { width:100%;max-width:700px;margin-top:3rem;box-shadow:0 30px 60px -12px rgba(0,0,0,0.85);background:rgba(255,255,255,0.04);border-radius:20px;padding:2.5rem 2.5rem;display:flex;flex-direction:column;align-items:center; }
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
			<li><a href="/projet/Gestion-absences/app/hello/student/attendance.php" class="sidebar-link"><i class="bi bi-calendar-check"></i><span>My Attendance</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/modules.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
			<li><a href="/projet/Gestion-absences/app/hello/student/absences.php" class="sidebar-link active"><i class="bi bi-exclamation-circle"></i><span>My Absences</span></a></li>
			<li><a href="/projet/Gestion-absences/public/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
		</ul>
	</aside>
	<main class="main-content">
		<div class="info-card" style="margin-bottom:2.5rem;background:linear-gradient(135deg,#ff3b3b22 0%,#00f5ff22 100%);box-shadow:0 8px 32px rgba(0,0,0,0.18);">
			<div style="display:flex;align-items:center;gap:1.5rem;width:100%;">
				<div style="width:70px;height:70px;background:linear-gradient(135deg,#ff3b3b,#00f5ff);border-radius:50%;display:grid;place-items:center;font-size:2.2rem;box-shadow:0 0 30px #ff3b3b44;">
					<i class="bi bi-exclamation-circle" style="color:white;"></i>
				</div>
				<div>
					<h1 style="font-size:2rem;font-weight:900;margin:0;color:#ff3b3b;letter-spacing:-1px;">My Absences</h1>
					<div style="color:#cbd5e1;font-size:1.1rem;margin-top:0.3rem;">Track your absences by module and stay on top of your academic progress. Every day counts!</div>
				</div>
			</div>
		</div>
		<div class="info-card" style="margin-bottom:2.5rem;">
			<h3 style="font-size:1.3rem;font-weight:700;color:#ff3b3b;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;"><i class="bi bi-bar-chart"></i> Absence Overview</h3>
			<?php if($absences && $absences->num_rows > 0): ?>
				<div style="width:100%;margin-bottom:2rem;">
					<svg width="100%" height="120">
						<?php 
						$max = 0;
						$bars = [];
						while($row = $absences->fetch_assoc()) {
							$bars[] = $row;
							if($row['count'] > $max) $max = $row['count'];
						}
						$barWidth = 50;
						$gap = 30;
						foreach($bars as $i => $row):
							$x = $i * ($barWidth + $gap);
							$height = $max ? ($row['count'] / $max * 90) : 0;
						?>
						<rect x="<?php echo $x; ?>" y="<?php echo 100-$height; ?>" width="<?php echo $barWidth; ?>" height="<?php echo $height; ?>" fill="#ff3b3b" rx="8" style="filter:drop-shadow(0 2px 12px #ff3b3b44);transition:height 0.7s;" />
						<text x="<?php echo $x + $barWidth/2; ?>" y="115" text-anchor="middle" font-size="1rem" fill="#f0f4f8"><?php echo htmlspecialchars($row['module_name']); ?></text>
						<text x="<?php echo $x + $barWidth/2; ?>" y="<?php echo 100-$height-8; ?>" text-anchor="middle" font-size="1.1rem" fill="#00f5ff" style="font-weight:700;"><?php echo $row['count']; ?></text>
						<?php endforeach; ?>
					</svg>
				</div>
			<?php else: ?>
				<div style="display:flex;flex-direction:column;align-items:center;gap:1.2rem;opacity:0.8;">
					<i class="bi bi-emoji-smile" style="font-size:2.5rem;color:#00f5ff;"></i>
					<p style="color: #94a3b8; font-size: 1.1rem;">No absences recorded – keep up the great attendance!</p>
				</div>
			<?php endif; ?>
		</div>
		<div class="info-card" style="background:linear-gradient(135deg,#00f5ff22 0%,#7b2ff722 100%);margin-bottom:2.5rem;">
			<h3 style="font-size:1.3rem;font-weight:700;color:#00f5ff;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;"><i class="bi bi-lightbulb"></i> Did You Know?</h3>
			<blockquote style="font-size:1.15rem;color:#cbd5e1;font-style:italic;margin:0 0 0.5rem 0;">“Students with regular attendance are more likely to achieve higher grades and build strong academic habits.”</blockquote>
			<div style="color:#94a3b8;font-size:0.98rem;">Your presence matters. Every day in class is a step toward your goals!</div>
		</div>
		<div class="info-card" style="background:rgba(255,255,255,0.03);">
			<h3 style="font-size:1.1rem;font-weight:700;color:#ff3b3b;margin-bottom:0.7rem;display:flex;align-items:center;gap:0.5rem;"><i class="bi bi-info-circle"></i> Absence Tips</h3>
			<ul style="color:#cbd5e1;font-size:1rem;line-height:1.7;margin:0 0 0 1.2rem;">
				<li>If you must miss a class, notify your professor in advance.</li>
				<li>Review missed material as soon as possible to stay on track.</li>
				<li>Use the chatbot for help catching up on missed content.</li>
			</ul>
		</div>
	</main>
</body>
</html>
