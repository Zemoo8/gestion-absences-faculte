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
:root {
    --primary: #00f5ff;
    --primary-glow: rgba(0, 245, 255, 0.5);
    --secondary: #7b2ff7;
    --accent: #f72b7b;
    --bg-main: linear-gradient(135deg, #0a0e27 0%, #12172f 50%, #1a1f3a 100%);
    --bg-panel: rgba(10, 14, 39, 0.7);
    --bg-card: rgba(255, 255, 255, 0.04);
    --bg-card-border: rgba(255, 255, 255, 0.08);
    --text-primary: #f0f4f8;
    --text-secondary: #cbd5e1;
    --text-muted: #94a3b8;
    --error: #ff3b3b;
    --success: #00e676;
    --warning: #ffa500;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    --glass-blur: blur(24px) saturate(200%);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html { scroll-behavior: smooth; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-main);
    background-size: 400% 400%;
    animation: gradientShift 25s ease infinite;
    color: var(--text-primary);
    overflow-x: hidden;
    min-height: 100vh;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    height: 70px;
    background: var(--bg-panel);
    backdrop-filter: var(--glass-blur);
    border-bottom: 1px solid var(--bg-card-border);
    display: flex;
    align-items: center;
    padding: 0 2rem;
    justify-content: space-between;
}

.navbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: var(--primary);
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: var(--transition);
}

.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.05); }

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--text-primary);
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 20px var(--primary-glow); }
    50% { transform: scale(1.08); box-shadow: 0 0 40px var(--primary-glow); }
}

.logo h1 {
    font-size: 1.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--text-primary), var(--primary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.navbar-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: var(--text-secondary);
    font-weight: 600;
}

.user-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--secondary), var(--accent));
    display: grid;
    place-items: center;
    font-size: 1rem;
    font-weight: 700;
}

.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
    margin-top: 70px;
}

.sidebar {
    width: 280px;
    background: var(--bg-panel);
    border-right: 1px solid var(--bg-card-border);
    padding: 1.5rem 0;
    position: fixed;
    left: 0;
    top: 70px;
    height: calc(100vh - 70px);
    overflow-y: auto;
    transition: var(--transition);
    z-index: 999;
}

.sidebar.collapsed { transform: translateX(-100%); }

.sidebar-menu { list-style: none; }

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 500;
    border-radius: 0 12px 12px 0;
    transition: var(--transition);
}

.sidebar-link.active {
    color: var(--primary);
    background: rgba(255, 255, 255, 0.04);
}

.sidebar-link:hover {
    background: rgba(255, 255, 255, 0.02);
}

.sidebar-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    transition: var(--transition);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    min-height: 80vh;
    width: 100%;
}

.sidebar.collapsed ~ .main-content { margin-left: 0; }

.info-card {
    width:100%;
    max-width:700px;
    margin-top:3rem;
    box-shadow:var(--shadow);
    background:var(--bg-card);
    backdrop-filter:var(--glass-blur);
    border:1px solid var(--bg-card-border);
    border-radius:20px;
    padding:2.5rem 2.5rem;
    display:flex;
    flex-direction:column;
    align-items:center;
}

@media (max-width: 768px) {
    .navbar { padding: 0 1rem; }
    .sidebar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        top: auto;
        height: auto;
        width: 100%;
        border-top: 1px solid var(--bg-card-border);
        border-right: none;
        padding: 0.5rem 0;
    }
    .sidebar.collapsed { transform: translateY(100%); }
    .sidebar-menu {
        display: flex;
        overflow-x: auto;
        padding: 0 1rem;
        justify-content: space-around;
    }
    .sidebar-link {
        flex-direction: column;
        padding: 0.5rem;
        font-size: 0.7rem;
        min-width: 70px;
    }
    .sidebar-link span { display: none; }
    .main-content {
        margin-left: 0;
        padding: 1rem;
        padding-bottom: 100px;
        flex-direction: column;
    }
}
</style>
</head>
<body>

<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <a href="/projet/Gestion-absences/public/index.php/studdash/dashstud" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <div class="user-menu">
            <span><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></span>
            <div class="user-avatar"><?php echo substr($_SESSION['prenom'], 0, 1); ?></div>
        </div>
    </div>
</nav>

<div class="dashboard-wrapper">
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
</div>

<script>
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});
</script>

</body>
</html>