<?php
// Ensure bootstrap is loaded when this view is accessed directly or via controller.
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/../../../bootstrap.php';
}

// If accessed directly, redirect to front-controller professor profile route
if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php') {
    $base = defined('PUBLIC_URL') ? PUBLIC_URL : ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    header('Location: ' . $base . '/index.php/profdash/profile');
    exit();
}

// Make DB connection available
global $mysqli;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

$prof_id = (int)$_SESSION['user_id'];
$success = '';
$error = '';

$upload_dir = __DIR__ . '/../../../public/assets/uploads/profiles/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo'])) {
    $user = $mysqli->query("SELECT photo_path FROM users WHERE id = $prof_id")->fetch_assoc();
    if ($user && !empty($user['photo_path'])) {
        $old_path = __DIR__ . '/../../../public/' . $user['photo_path'];
        if (strpos($old_path, realpath(__DIR__ . '/../../../public/assets/uploads/profiles/')) === 0 && file_exists($old_path)) {
            @unlink($old_path);
        }
        $stmt = $mysqli->prepare("UPDATE users SET photo_path=NULL WHERE id=?");
        $stmt->bind_param("i", $prof_id);
        $stmt->execute();
        $success = 'Profile photo removed successfully.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['photo']['size'] > $max_size) {
            $error = 'Photo size must be less than 5MB.';
        } elseif (!in_array($_FILES['photo']['type'], $allowed_types)) {
            $error = 'Please upload a valid image (JPG, PNG, GIF, or WebP).';
        } else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $prof_id . '_' . time() . '.' . $ext;
            $file_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
                $photo_path = 'assets/uploads/profiles/' . $filename;
                $stmt = $mysqli->prepare("UPDATE users SET photo_path=? WHERE id=?");
                $stmt->bind_param("si", $photo_path, $prof_id);
                $stmt->execute();
                $success = 'Profile photo updated successfully.';
            } else {
                $error = 'Failed to upload photo. Please try again.';
            }
        }
    } else {
        $error = 'Photo upload error. Please try again.';
    }
}

$user = $mysqli->query("SELECT nom, prenom, email, role, photo_path FROM users WHERE id = $prof_id")->fetch_assoc();
$public_url = defined('PUBLIC_URL') ? PUBLIC_URL : 'http://localhost';
$photo_url = (!empty($user['photo_path']) && file_exists(__DIR__ . '/../../../public/' . $user['photo_path']))
    ? $public_url . '/' . $user['photo_path']
    : null;

$profile_score = $photo_url ? 100 : 70;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | macademia Faculty</title>
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
    --warning: #ffaa00;
    --shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.85);
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
    --glass-blur: blur(24px) saturate(200%);
}
:root[data-theme="light"] {
    --primary: #8B5E3C;
    --primary-glow: rgba(139,94,60,0.12);
    --secondary: #3B6A47;
    --accent: #A67C52;
    --bg-main: linear-gradient(180deg, #f4efe6 0%, #efe7d9 100%);
    --bg-panel: rgba(255, 255, 255, 0.9);
    --bg-card: #ffffff;
    --bg-card-border: rgba(0, 0, 0, 0.06);
    --text-primary: #2b2b2b;
    --text-secondary: #4b4b4b;
    --text-muted: #6b6b6b;
    --error: #c53030;
    --success: #2f855a;
    --warning: #d97706;
    --shadow: 0 12px 30px rgba(15,15,15,0.08);
    --transition: all 0.3s ease;
    --glass-blur: blur(8px) saturate(120%);
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg-main);
    background-size: 400% 400%;
    animation: gradientShift 25s ease infinite;
    color: var(--text-primary);
    min-height: 100vh;
}
@keyframes gradientShift { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
.navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000; height: 70px;
    background: var(--bg-panel); backdrop-filter: var(--glass-blur);
    border-bottom: 1px solid var(--bg-card-border); display: flex; align-items: center;
    padding: 0 2rem; justify-content: space-between;
}
.navbar-left { display: flex; align-items: center; gap: 1rem; }
.sidebar-toggle { background: none; border: none; color: var(--primary); font-size: 1.5rem; cursor: pointer; padding: 0.5rem; border-radius: 8px; transition: var(--transition); }
.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.05); }
.logo { display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: var(--text-primary); }
.logo-icon { width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 10px; display: grid; place-items: center; font-size: 1.25rem; }
.logo h1 { font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, var(--text-primary), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.navbar-right { display: flex; align-items: center; gap: 1.5rem; }
.notification-wrapper { position: relative; }
.notification-bell { background: none; border: none; color: var(--text-secondary); font-size: 1.25rem; cursor: pointer; padding: 0.5rem; border-radius: 50%; transition: var(--transition); position: relative; }
.notification-bell:hover { background: rgba(255, 255, 255, 0.05); color: var(--primary); }
.notification-dropdown {
    position: absolute; top: 100%; right: 0; margin-top: 1rem;
    background: var(--bg-panel); border: 1px solid var(--bg-card-border);
    border-radius: 14px; min-width: 320px; box-shadow: var(--shadow);
    opacity: 0; visibility: hidden; transform: translateY(-10px);
    transition: var(--transition); z-index: 1001;
}
.notification-dropdown.show { opacity: 1; visibility: visible; transform: translateY(0); }
.dropdown-empty { padding: 1rem; text-align: center; color: var(--text-muted); }
.theme-toggle { background: rgba(255, 255, 255, 0.1); border: 1px solid var(--bg-card-border); color: var(--text-secondary); padding: 0.5rem 1rem; border-radius: 12px; cursor: pointer; font-size: 1rem; transition: var(--transition); display: flex; align-items: center; gap: 0.5rem; }
.theme-toggle:hover { background: rgba(255, 255, 255, 0.15); border-color: var(--primary); color: var(--primary); transform: translateY(-2px); }
.user-menu { display: flex; align-items: center; gap: 1rem; color: var(--text-secondary); font-weight: 600; }
.user-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--secondary), var(--accent)); display: grid; place-items: center; font-weight: 700; color: white; }
.dashboard-wrapper { display: flex; min-height: 100vh; margin-top: 70px; }
.sidebar { width: 280px; background: var(--bg-panel); border-right: 1px solid var(--bg-card-border); padding: 1.5rem 0; position: fixed; left: 0; top: 70px; height: calc(100vh - 70px); overflow-y: auto; transition: var(--transition); z-index: 999; }
.sidebar.collapsed { transform: translateX(-100%); }
.sidebar-menu { list-style: none; }
.sidebar-link { display: flex; align-items: center; gap: 1rem; padding: 1rem 1.5rem; color: var(--text-secondary); text-decoration: none; font-weight: 500; border-radius: 0 12px 12px 0; transition: var(--transition); }
.sidebar-link.active { color: var(--primary); background: rgba(255, 255, 255, 0.04); }
:root[data-theme="light"] .sidebar-link.active { background: rgba(139, 94, 60, 0.08); }
.sidebar-link:hover { background: rgba(255, 255, 255, 0.02); color: var(--text-primary); }
:root[data-theme="light"] .sidebar-link:hover { background: rgba(139, 94, 60, 0.08); }
.sidebar-link i { font-size: 1.25rem; width: 24px; text-align: center; }
.main-content { flex: 1; margin-left: 280px; padding: 2rem; transition: var(--transition); }
.sidebar.collapsed ~ .main-content { margin-left: 0; }
.page-header { margin-bottom: 2rem; }
.page-title { font-size: 2.2rem; font-weight: 800; }
.page-subtitle { color: var(--text-secondary); }
.profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
@media (max-width: 900px) { .profile-grid { grid-template-columns: 1fr; } }
.card { background: var(--bg-card); border: 1px solid var(--bg-card-border); border-radius: 18px; padding: 2rem; box-shadow: var(--shadow); }
.profile-card { display: flex; flex-direction: column; align-items: center; gap: 1rem; text-align: center; }
.profile-photo { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; object-position: center; border: 3px solid var(--primary); box-shadow: 0 0 25px var(--primary-glow); }
.profile-initials { width: 130px; height: 130px; border-radius: 50%; display: grid; place-items: center; font-size: 2.4rem; font-weight: 800; color: white; background: linear-gradient(135deg, var(--primary), var(--accent)); }
.badge { padding: 0.4rem 0.75rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
.badge.role { background: rgba(0,245,255,0.1); color: var(--primary); border: 1px solid var(--primary); }
.info-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--bg-card-border); }
.info-row:last-child { border-bottom: none; }
.label { color: var(--text-muted); font-weight: 600; }
.value { color: var(--text-primary); font-weight: 600; }
.progress { height: 10px; background: rgba(255,255,255,0.08); border-radius: 999px; overflow: hidden; }
.progress-bar { height: 100%; background: linear-gradient(135deg, var(--primary), var(--accent)); }
.upload-box { border: 2px dashed var(--primary); padding: 1.5rem; border-radius: 14px; text-align: center; }
.upload-box input { display: none; }
.upload-label { display: inline-flex; gap: 0.5rem; align-items: center; cursor: pointer; color: var(--primary); font-weight: 600; }
.upload-actions { display: flex; gap: 1rem; margin-top: 1rem; flex-wrap: wrap; }
.btn { padding: 0.75rem 1.25rem; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; transition: var(--transition); }
.btn-primary { background: linear-gradient(135deg, var(--primary), var(--accent)); color: var(--bg-main); }
.btn-secondary { background: rgba(255,255,255,0.05); color: var(--text-secondary); border: 1px solid var(--bg-card-border); }
.btn-danger { background: rgba(255,59,59,0.1); color: var(--error); border: 1px solid var(--error); }
.alert { padding: 0.75rem 1rem; border-radius: 10px; margin-bottom: 1rem; }
.alert-success { background: rgba(0, 230, 118, 0.12); color: var(--success); border: 1px solid var(--success); }
.alert-danger { background: rgba(255, 59, 59, 0.12); color: var(--error); border: 1px solid var(--error); }
.features { display: grid; gap: 1rem; }
.feature-item { display: flex; gap: 0.75rem; align-items: center; }
.feature-item i { color: var(--primary); }
</style>
</head>
<body>
<nav class="navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></button>
        <a href="<?php echo $public_url; ?>/index.php/profdash" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    <div class="navbar-right">
        <button class="theme-toggle" id="themeToggle" title="Toggle theme">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
        <div class="notification-wrapper">
            <button class="notification-bell" id="notificationBell"><i class="bi bi-bell-fill"></i></button>
            <div class="notification-dropdown" id="notificationDropdown"><p class="dropdown-empty">No new notifications</p></div>
        </div>
        <div class="user-menu">
            <span>Prof. <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Professor'); ?></span>
            <?php if ($photo_url): ?>
                <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Profile" class="profile-photo" style="width:38px;height:38px;">
            <?php else: ?>
                <div class="user-avatar"><?php echo substr($_SESSION['user_name'] ?? 'P', 0, 1); ?></div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="dashboard-wrapper">
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="<?php echo $public_url; ?>/index.php/profdash/profile" class="sidebar-link active"><i class="bi bi-person-circle"></i><span>Profile</span></a></li>
            <li><a href="<?php echo $public_url; ?>/index.php/profdash" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="<?php echo $public_url; ?>/index.php/profdash/my_modules" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>My Modules</span></a></li>
            <li><a href="<?php echo $public_url; ?>/index.php/profdash/students" class="sidebar-link"><i class="bi bi-people"></i><span>Students</span></a></li>
            <li><a href="<?php echo $public_url; ?>/index.php/profdash/reports" class="sidebar-link"><i class="bi bi-graph-up"></i><span>Reports</span></a></li>
            <li><a href="<?php echo $public_url; ?>/index.php/login/logout" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">My Profile</h1>
            <p class="page-subtitle">Keep your faculty profile up to date</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="card profile-card">
                <?php if ($photo_url): ?>
                    <img src="<?php echo htmlspecialchars($photo_url); ?>" alt="Profile" class="profile-photo">
                <?php else: ?>
                    <div class="profile-initials"><?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?></div>
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h2>
                <span class="badge role">Professor</span>
                <div style="width:100%; text-align:left; margin-top: 1rem;">
                    <div class="label" style="margin-bottom:0.5rem;">Profile completeness</div>
                    <div class="progress"><div class="progress-bar" style="width: <?php echo $profile_score; ?>%"></div></div>
                    <div class="label" style="margin-top:0.5rem;"><?php echo $profile_score; ?>%</div>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1rem;">Account Information</h3>
                <div class="info-row"><span class="label">Full Name</span><span class="value"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></span></div>
                <div class="info-row"><span class="label">Email</span><span class="value"><?php echo htmlspecialchars($user['email']); ?></span></div>
                <div class="info-row"><span class="label">Role</span><span class="value"><?php echo ucfirst($user['role']); ?></span></div>

                <div style="margin-top: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">Update Profile Photo</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="upload-box">
                            <input type="file" id="photo" name="photo" accept="image/*">
                            <label class="upload-label" for="photo"><i class="bi bi-cloud-upload"></i> Choose a photo (JPG/PNG/GIF/WebP)</label>
                            <div style="margin-top:0.5rem; color: var(--text-muted); font-size: 0.85rem;">Max size 5MB</div>
                        </div>
                        <div class="upload-actions">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Photo</button>
                            <?php if ($photo_url): ?>
                                <button type="submit" name="remove_photo" value="1" class="btn btn-danger"><i class="bi bi-trash"></i> Remove Photo</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1rem;">Faculty Insights</h3>
                <div class="features">
                    <div class="feature-item"><i class="bi bi-lightning"></i> Theme preference is saved automatically.</div>
                    <div class="feature-item"><i class="bi bi-envelope-check"></i> Your email is used for important alerts.</div>
                    <div class="feature-item"><i class="bi bi-shield-lock"></i> Keep your account secure with a strong password.</div>
                </div>
                <div class="upload-actions" style="margin-top:1rem;">
                    <a class="btn btn-secondary" href="<?php echo $public_url; ?>/index.php/login/forgot_password"><i class="bi bi-key"></i> Change Password</a>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));
}

const themeToggle = document.getElementById('themeToggle');
const themeIcon = document.getElementById('themeIcon');
const root = document.documentElement;
const savedTheme = localStorage.getItem('theme') || 'dark';
if (savedTheme === 'light') {
    root.setAttribute('data-theme', 'light');
    themeIcon.classList.remove('bi-moon-fill');
    themeIcon.classList.add('bi-sun-fill');
}
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        const currentTheme = root.getAttribute('data-theme');
        if (currentTheme === 'light') {
            root.removeAttribute('data-theme');
            localStorage.setItem('theme', 'dark');
            themeIcon.classList.remove('bi-sun-fill');
            themeIcon.classList.add('bi-moon-fill');
        } else {
            root.setAttribute('data-theme', 'light');
            localStorage.setItem('theme', 'light');
            themeIcon.classList.remove('bi-moon-fill');
            themeIcon.classList.add('bi-sun-fill');
        }
    });
}

const notificationBell = document.getElementById('notificationBell');
const notificationDropdown = document.getElementById('notificationDropdown');
if (notificationBell && notificationDropdown) {
    notificationBell.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationDropdown.classList.toggle('show');
    });
    document.addEventListener('click', (e) => {
        if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
            notificationDropdown.classList.remove('show');
        }
    });
}
</script>
</body>
</html>
