<?php
// Bootstrap loads config and starts session; view remains presentation-only.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

// Pending requests for notification bell
$pending_requests = $mysqli->query("
    SELECT id, nom, prenom, email, created_at 
    FROM account_requests 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5"
);

$msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if(empty($nom) || empty($prenom) || empty($email)) {
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>All fields required!</div>";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Invalid email!</div>";
    } else {
        $check = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Email exists!</div>";
        } else {
            $pass = bin2hex(random_bytes(4));
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nom, $prenom, $email, $hashed, $role);
            if($stmt->execute()){
                $msg = "<div style='color:#00e676;margin-bottom:1rem;'>User created! Password: <strong>$pass</strong></div>";
            }
        }
    }
}
?>
<?php


if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: " . PUBLIC_URL . "/index.php/login/login");
    exit();
}

// Pending requests for notification bell
$pending_requests = $mysqli->query("
    SELECT id, nom, prenom, email, created_at 
    FROM account_requests 
    WHERE status = 'pending' 
    ORDER BY created_at DESC 
    LIMIT 5"
);

$msg = "";

// Approve request from notification dropdown
if(isset($_GET['email'])){
    $req_email = $_GET['email'];
    
    // Get request data
    $stmt = $mysqli->prepare("SELECT id, nom, prenom, email FROM account_requests WHERE email=? AND status='pending'");
    $stmt->bind_param("s", $req_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 1){
        $request = $result->fetch_assoc();
        $nom = $request['nom'];
        $prenom = $request['prenom'];
        $user_email = $request['email']; // where we send credentials
        
        // Generate login email and password
        $generated_email = strtolower($nom . $prenom . rand(10,99)) . ".macademia@gmail.com";
        $generated_pass = bin2hex(random_bytes(4));
        $hashed_pass = password_hash($generated_pass, PASSWORD_DEFAULT);
        $role = 'student';

        // Insert into users table
        $stmt2 = $mysqli->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssss", $nom, $prenom, $generated_email, $hashed_pass, $role);
        if($stmt2->execute()){
            // Mark request as approved
            $stmt3 = $mysqli->prepare("UPDATE account_requests SET status='approved' WHERE id=?");
            $stmt3->bind_param("i", $request['id']);
            $stmt3->execute();

            // Send email with credentials to the requested email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'farouk.zemoo@gmail.com'; // your sending Gmail
                $mail->Password = 'kibh ehzs ofxg zpem'; // use App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty System');
                $mail->addAddress($user_email); // send to requested email

                $mail->isHTML(true);
                $mail->Subject = 'Your Faculty Account Credentials';
                $mail->Body = "
                    <h2>Account Approved</h2>
                    <p>Hi <strong>{$nom} {$prenom}</strong>, your account has been created!</p>
                    <p><strong>Login Email:</strong> {$generated_email}</p>
                    <p><strong>Password:</strong> {$generated_pass}</p>
                    <p>Please log in and change your password after first login.</p>
                ";

                $mail->send();
                $msg = "<div style='color:#00e676;margin-bottom:1rem;'>Account approved and credentials sent to {$user_email}!</div>";
            } catch (Exception $e) {
                $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>User created but email failed: {$mail->ErrorInfo}</div>";
            }
        } else {
            $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Failed to create user!</div>";
        }
    } else {
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Request not found or already approved!</div>";
    }
}

// Manual user creation via form
if($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_GET['email'])){
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if(empty($nom) || empty($prenom) || empty($email)) {
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>All fields required!</div>";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Invalid email!</div>";
    } else {
        $check = $mysqli->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Email exists!</div>";
        } else {
            $pass = bin2hex(random_bytes(4));
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nom, $prenom, $email, $hashed, $role);
            if($stmt->execute()){
                $msg = "<div style='color:#00e676;margin-bottom:1rem;'>User created! Password: <strong>$pass</strong></div>";
            } else {
                $msg = "<div style='color:#ff3b3b;margin-bottom:1rem;'>Failed to create user!</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>Add User | macademia Faculty</title>
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

/* === FIXED NAVBAR === */
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

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.05);
}

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

/* Notification Dropdown */
.notification-wrapper {
    position: relative;
}

.notification-bell {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: var(--transition);
    position: relative;
}

.notification-bell:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--primary);
}

.notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: var(--error);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    animation: pulseBadge 2s ease infinite;
}

.notification-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 1rem;
    backdrop-filter: none;
    border: 1px solid var(--bg-card-border);
    border-radius: 14px;
    min-width: 320px;
    max-width: 400px;
    box-shadow: var(--shadow);
    opacity: 1;
    visibility: hidden;
    transform: translateY(-10px);
    transition: var(--transition);
    z-index: 1001;
}

.notification-dropdown.show {
    background: #0a0e27;
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
}

.btn-small {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
}

.notification-item {
    padding: 1rem;
    border-bottom: 1px solid var(--bg-card-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: var(--transition);
}

.notification-item:hover {
    background: rgba(255, 255, 255, 0.03);
}

.notification-content strong {
    color: var(--primary);
    font-size: 0.875rem;
}

.notification-content p {
    margin: 0.25rem 0;
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.notification-content small {
    color: var(--text-muted);
    font-size: 0.75rem;
}

.btn-approve {
    background: rgba(0, 230, 118, 0.1);
    color: var(--success);
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid rgba(0, 230, 118, 0.2);
}

.dropdown-empty {
    padding: 1rem;
    text-align: center;
    color: var(--text-muted);
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

/* === MAIN LAYOUT === */
.dashboard-wrapper {
    display: flex;
    min-height: 100vh;
    margin-top: 70px;
}

/* === SIDEBAR === */
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

.sidebar.collapsed {
    transform: translateX(-100%);
}

.sidebar-menu {
    list-style: none;
}

.sidebar-item {
    margin-bottom: 0.25rem;
}

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

.sidebar-link i {
    font-size: 1.25rem;
    width: 24px;
    text-align: center;
}

/* === MAIN CONTENT === */
.main-content {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    transition: var(--transition);
}

.sidebar.collapsed ~ .main-content {
    margin-left: 0;
}

/* === FORM STYLES === */
.form-box {
    max-width: 500px;
    width: 100%;
    background: var(--bg-card);
    backdrop-filter: var(--glass-blur);
    border: 1px solid var(--bg-card-border);
    border-radius: 20px;
    padding: 2rem;
    margin: 0 auto;
}

.form-box h2 {
    color: var(--primary);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    font-weight: 700;
}

.form-box input, .form-box select {
    width: 100%;
    padding: 0.75rem;
    margin-bottom: 1rem;
    border: 2px solid var(--bg-card-border);
    border-radius: 10px;
    background: #12172f; /* darker solid background */
    color: #f0f4f8; 
    font-family: 'Inter', sans-serif;
}

.form-box button {
    width: 100%;
    padding: 0.75rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border: none;
    color: white;
    font-weight: 700;
    border-radius: 10px;
    cursor: pointer;
    transition: var(--transition);
}

.form-box button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px var(--primary-glow);
}

/* === ANIMATIONS === */
@keyframes pulseBadge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

/* === RESPONSIVE === */
@media (max-width: 768px) {
    .navbar {
        padding: 0 1rem;
    }
    
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
    
    .sidebar.collapsed {
        transform: translateY(100%);
    }
    
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
    
    .sidebar-link span {
        display: none;
    }
    
    .main-content {
        margin-left: 0;
        padding: 1rem;
        padding-bottom: 100px;
    }
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
        <a href="dashboard.php" class="logo">
            <div class="logo-icon"><i class="bi bi-mortarboard-fill"></i></div>
            <h1>macademia Faculty</h1>
        </a>
    </div>
    
    <div class="navbar-right">
        <!-- Notification Dropdown -->
        <div class="notification-wrapper">
            <button class="notification-bell" id="notificationBell">
                <i class="bi bi-bell-fill"></i>
                <?php if($pending_requests->num_rows > 0): ?>
                <span class="notification-badge"><?php echo $pending_requests->num_rows; ?></span>
                <?php endif; ?>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <?php if($pending_requests->num_rows > 0): ?>
                    <div class="dropdown-header">
                        <strong>Account Requests</strong>
                        <a href="adduser.php" class="btn-small">+ Add User</a>
                    </div>
                    <?php while($req = $pending_requests->fetch_assoc()): ?>
                    <div class="notification-item">
                        <div class="notification-content">
                            <strong><?php echo htmlspecialchars($req['nom'] . ' ' . $req['prenom']); ?></strong>
                            <p><?php echo htmlspecialchars($req['email']); ?></p>
                            <small><?php echo date('M d, H:i', strtotime($req['created_at'])); ?></small>
                        </div>
                        <a href="adduser.php?email=<?php echo urlencode($req['email']); ?>" class="btn-approve">Approve</a>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="dropdown-empty">No new notifications</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="user-menu">
            <span>Farouk</span>
            <div class="user-avatar">F</div>
        </div>
    </div>
</nav>

<!-- === WRAPPER === -->
<div class="dashboard-wrapper">
    <!-- === SIDEBAR === -->
    <aside class="sidebar" id="sidebar">
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li><a href="adduser.php" class="sidebar-link active"><i class="bi bi-person-plus"></i><span>Add User</span></a></li>
            <li><a href="userlist.php" class="sidebar-link"><i class="bi bi-people"></i><span>User List</span></a></li>
            <li><a href="addmodule.php" class="sidebar-link"><i class="bi bi-bookmark-plus"></i><span>Add Module</span></a></li>
            <li><a href="modulelist.php" class="sidebar-link"><i class="bi bi-bookshelf"></i><span>Module List</span></a></li>
                <li><a href="classes.php" class="sidebar-link"><i class="bi bi-collection"></i><span>Manage Classes</span></a></li>
            <li><a href="assign_students.php" class="sidebar-link"><i class="bi bi-person-check"></i><span>Assign Students</span></a></li>
            <li><a href="attendancerecord.php" class="sidebar-link"><i class="bi bi-clipboard-data"></i><span>Attendance</span></a></li>
            <li><a href="notif.php" class="sidebar-link"><i class="bi bi-bell"></i><span>Notifications</span></a></li>
            <li><a href="logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- === MAIN CONTENT === -->
    <main class="main-content">
        <div class="form-box">
            <h2>Add User</h2>
            <?php echo $msg; ?>
            <form method="POST">
                <input type="text" name="nom" placeholder="First Name" required>
                <input type="text" name="prenom" placeholder="Last Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="role" required>
                    <option value="student">Student</option>
                    <option value="professor">Professor</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Create User</button>
            </form>
        </div>
    </main>
</div>

<script>
// === SIDEBAR TOGGLE ===
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');

sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
});

// === NOTIFICATION DROPDOWN ===
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