<?php
session_start();
require_once '../login/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login/login.php");
    exit();
}

$prof_id = (int)$_SESSION['user_id'];
$prof_info = $mysqli->query("SELECT nom, prenom FROM users WHERE id = $prof_id")->fetch_assoc();

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* Reminder log table */
$mysqli->query("CREATE TABLE IF NOT EXISTS reminder_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    professor_id INT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_student_prof (student_id, professor_id),
    KEY idx_date (sent_at)
)");

/* ---------- SEND REMINDER ---------- */
if (isset($_POST['remind']) && isset($_POST['student_id'])) {
    header('Content-Type: application/json');
    $sid = (int)$_POST['student_id'];

    $stat = $mysqli->query("
        SELECT COUNT(*) total, SUM(status='absent') absent, u.email, u.nom, u.prenom
        FROM attendance a
        JOIN users u ON u.id = a.student_id
        WHERE a.student_id = $sid
          AND a.module_id IN (SELECT id FROM modules WHERE professor_id = $prof_id)
    ")->fetch_assoc();

    if (!$stat || !$stat['total']) {
        echo json_encode(['ok'=>false,'reason'=>'No attendance data']);
        exit();
    }

    $absRate = ($stat['absent'] / $stat['total']) * 100;

    if ($absRate < 25) {
        echo json_encode(['ok'=>false,'reason'=>'Below 25% threshold']);
        exit();
    }

    $today = date('Y-m-d');
    $already = $mysqli->query("SELECT 1 FROM reminder_log WHERE student_id=$sid AND professor_id=$prof_id AND DATE(sent_at)='$today'")->num_rows;
    if ($already) {
        echo json_encode(['ok'=>false,'reason'=>'Already reminded today']);
        exit();
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'farouk.zemoo@gmail.com';
        $mail->Password = 'kibh ehzs ofxg zpem';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('farouk.zemoo@gmail.com', 'macademia Faculty System');
        $mail->addAddress($stat['email'], $stat['prenom'].' '.$stat['nom']);
        $mail->isHTML(true);
        $mail->Subject = 'Attendance Reminder';
        $mail->Body = '
        <div style="font-family:Inter,sans-serif;max-width:600px;margin:auto;background:#0a0e27;color:#f0f4f8;border-radius:12px;overflow:hidden">
          <div style="padding:30px;">
            <h2 style="color:#00f5ff;margin-top:0;">Attendance Alert</h2>
            <p>Hi <strong>'.htmlspecialchars($stat['prenom']).'</strong>,</p>
            <p>Your absence rate is <strong>'.round($absRate,1).'%</strong> in modules taught by Prof. '.htmlspecialchars($prof_info['prenom'].' '.$prof_info['nom']).'.</p>
            <p>Please attend upcoming classes to avoid academic penalties.</p>
          </div>
        </div>';

        $mail->send();
        $mysqli->query("INSERT INTO reminder_log (student_id, professor_id, sent_at) VALUES ($sid, $prof_id, NOW())");

        echo json_encode(['ok'=>true,'sent'=>true]);

    } catch (Exception $e) {
        echo json_encode(['ok'=>false,'reason'=>'Mail error','msg'=>$mail->ErrorInfo]);
    }
    exit();
}

/* ---------- FETCH STUDENTS ---------- */
$students = $mysqli->query("
    SELECT u.id,u.nom,u.prenom,u.email,
           COALESCE(att.total_classes,0) total_classes,
           COALESCE(att.absences,0) absences,
           COALESCE(att.module_count,0) module_count
    FROM users u
    LEFT JOIN (
        SELECT a.student_id,
               COUNT(DISTINCT a.id) total_classes,
               SUM(CASE WHEN a.status='absent' THEN 1 ELSE 0 END) absences,
               COUNT(DISTINCT m.id) module_count
        FROM attendance a
        INNER JOIN modules m ON m.id=a.module_id
        WHERE m.professor_id=$prof_id
        GROUP BY a.student_id
    ) att ON att.student_id = u.id
    WHERE u.role='student'
    ORDER BY u.nom,u.prenom
");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<title>My Students | macademia Faculty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{--primary:#00f5ff;--accent:#f72b7b;--bg-main:linear-gradient(135deg,#0a0e27 0%,#12172f 50%);--bg-card:rgba(255,255,255,.04);--bg-card-border:rgba(255,255,255,.08);--text-primary:#f0f4f8;--text-muted:#94a3b8;--error:#ff3b3b;--success:#00e676;--shadow:0 10px 40px rgba(0,0,0,.7);--transition:.4s cubic-bezier(.25,.46,.45,.94);--glass-blur:blur(20px) saturate(180%);}
body{font-family:'Inter',sans-serif;background:var(--bg-main);color:var(--text-primary);min-height:100vh;margin:0;}
.epic-header{position:fixed;top:0;left:0;right:0;height:90px;background:rgba(10,14,39,.7);backdrop-filter:var(--glass-blur);border-bottom:1px solid var(--bg-card-border);z-index:10000;display:flex;align-items:center;padding:0 2rem;}
.logo-holo{display:flex;align-items:center;gap:1rem;text-decoration:none;color:var(--text-primary);}
.logo-icon-pulse{width:50px;height:50px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:12px;display:grid;place-items:center;font-size:1.5rem;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{transform:scale(1);}50%{transform:scale(1.08);}}
.logo-text{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900;}
.user-info{display:flex;align-items:center;gap:1rem;color:var(--text-secondary);}
.user-avatar{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--secondary),var(--accent));display:grid;place-items:center;font-weight:700;}
.page-header{margin-top:120px;margin-bottom:2rem;padding:0 2rem;max-width:1400px;margin-left:auto;margin-right:auto;}
.page-title{font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,3rem);background:linear-gradient(135deg,var(--text-primary),var(--primary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:.5rem;}
.page-subtitle{color:var(--text-muted);font-size:1.1rem;}
.toggle-container{display:flex;align-items:center;gap:1rem;margin-bottom:2rem;padding:1rem;background:var(--bg-card);border-radius:12px;border:1px solid var(--bg-card-border);max-width:1400px;margin-left:auto;margin-right:auto;}
.toggle-switch{position:relative;width:60px;height:30px;background:var(--bg-card-border);border-radius:15px;cursor:pointer;transition:var(--transition);}
.toggle-switch.active{background:var(--error);}
.toggle-knob{position:absolute;top:3px;left:3px;width:24px;height:24px;background:white;border-radius:50%;transition:var(--transition);}
.toggle-switch.active .toggle-knob{transform:translateX(30px);}
.students-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:2rem;max-width:1400px;margin:0 auto;padding:0 2rem;}
.student-card{background:var(--bg-card);backdrop-filter:var(--glass-blur);border:1px solid var(--bg-card-border);border-radius:20px;padding:2rem;transition:var(--transition);}
.student-card:hover{transform:translateY(-5px);box-shadow:var(--shadow);border-color:var(--primary);}
.student-card.critical-alert{border-color:var(--error);background:rgba(255,59,59,.05);}
.student-header{display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;}
.student-avatar{width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--secondary),var(--accent));display:grid;place-items:center;font-size:1.2rem;font-weight:700;}
.student-info h3{color:var(--text-primary);margin-bottom:.25rem;}
.student-email{color:var(--text-muted);font-size:.9rem;}
.module-count{display:inline-flex;align-items:center;gap:.5rem;background:rgba(0,245,255,.1);padding:.5rem 1rem;border-radius:50px;font-size:.9rem;color:var(--primary);margin-bottom:1rem;}
.attendance-display{text-align:center;padding:1.5rem;background:rgba(255,255,255,.02);border-radius:12px;margin-bottom:1rem;}
.rate-circle{position:relative;width:100px;height:100px;margin:0 auto 1rem;}
.rate-circle svg{transform:rotate(-90deg);}
.rate-circle-bg{fill:none;stroke:rgba(255,255,255,.1);stroke-width:8;}
.rate-circle-progress{fill:none;stroke-width:8;stroke-linecap:round;transition:stroke-dashoffset .5s ease;}
.rate-circle-progress.absence{stroke:var(--success);}
.rate-circle-progress.presence{stroke:var(--primary);}
.rate-percentage{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:1.5rem;font-weight:800;}
.rate-percentage.high{color:var(--success);}
.rate-percentage.medium{color:#ffaa00;}
.rate-percentage.low{color:var(--error);}
.rate-label{color:var(--text-muted);font-size:.9rem;}
.alert-banner{display:none;padding:.75rem 1rem;border-radius:8px;margin-top:1rem;align-items:center;gap:.5rem;font-size:.9rem;}
.alert-banner.critical{background:rgba(255,59,59,.1);border:1px solid var(--error);color:var(--error);}
.student-actions{display:flex;gap:.75rem;}
.btn-action{flex:1;padding:.75rem 1rem;border-radius:10px;font-weight:600;cursor:pointer;transition:var(--transition);text-decoration:none;text-align:center;font-size:.9rem;border:none;}
.btn-primary{background:linear-gradient(135deg,var(--primary),var(--accent));color:var(--bg-main);}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 5px 15px var(--primary-glow);}
.btn-secondary{background:rgba(255,255,255,.05);color:var(--text-secondary);border:1px solid var(--bg-card-border);}
.btn-secondary:hover{background:rgba(0,245,255,.1);color:var(--primary);}
.empty-state{text-align:center;padding:4rem 2rem;max-width:600px;margin:0 auto;}
.empty-state i{font-size:4rem;color:var(--text-muted);margin-bottom:1rem;}
.empty-state h3{color:var(--text-primary);margin-bottom:.5rem;}
</style>
</head>
<body>

<header class="epic-header" id="epicHeader">
<div class="header-container">
    <a href="dashboard.php" class="logo-holo"><div class="logo-icon-pulse"><i class="bi bi-mortarboard-fill"></i></div><span class="logo-text">macademia</span></a>
    <div class="user-info"><span class="user-name">Prof. <?= htmlspecialchars($prof_info['prenom'].' '.$prof_info['nom']) ?></span>
    <div class="user-avatar"><?= substr($prof_info['prenom'],0,1) ?></div></div>
</div>
</header>

<div class="page-header">
<h1 class="page-title">My Students</h1>
<p class="page-subtitle">Monitor attendance across all your modules</p>
</div>

<div class="toggle-container">
<span style="color:var(--error);font-weight:600;">Absence Rate</span>
<div class="toggle-switch active" id="displayToggle"><div class="toggle-knob"></div></div>
<span style="color:var(--text-secondary);">Presence Rate</span>
</div>

<div class="students-grid">
<?php if($students->num_rows>0): ?>
<?php while($s=$students->fetch_assoc()):
$total=$s['total_classes'];
$abs=$s['absences'];
$pres=$total-$abs;
$absRate=$total?($abs/$total*100):0;
$presRate=$total?($pres/$total*100):0;
$critical=$absRate>=25;
?>
<div class="student-card <?= $critical?'critical-alert':'' ?>" data-rates='{"absence":<?= $absRate ?>,"presence":<?= $presRate ?>}'>
<div class="student-header">
<div class="student-avatar"><?= substr($s['prenom'],0,1) ?></div>
<div class="student-info">
<h3><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></h3>
<div class="student-email"><?= htmlspecialchars($s['email']) ?></div>
</div></div>
<div class="module-count"><i class="bi bi-book-half"></i> <?= $s['module_count'] ?> enrolled modules</div>
<div class="attendance-display">
<div class="rate-circle">
<svg width="100" height="100"><circle class="rate-circle-bg" cx="50" cy="50" r="42"></circle>
<circle class="rate-circle-progress absence" cx="50" cy="50" r="42" stroke-dasharray="263.89" stroke-dashoffset="<?= 263.89-($absRate/100*263.89) ?>"></circle>
</svg>
<div class="rate-percentage <?= $absRate>=25?'low':($absRate>=15?'medium':'high') ?>" id="rate-<?= $s['id'] ?>"><?= round($absRate) ?>%</div>
</div>
<div class="rate-label" id="label-<?= $s['id'] ?>">Absence Rate</div>
<div class="alert-banner critical" id="alert-<?= $s['id'] ?>" style="<?= $critical?'display:flex':'display:none' ?>"><i class="bi bi-exclamation-triangle-fill"></i><span>25%+ absence detected</span></div>
</div>
<div class="student-actions">
<a href="student_detail.php?id=<?= $s['id'] ?>" class="btn-action btn-primary"><i class="bi bi-eye"></i> View Details</a>
<button class="btn-action btn-secondary" onclick="sendReminder(<?= $s['id'] ?>, event)"><i class="bi bi-envelope"></i> Send Reminder</button>
</div>
</div>
<?php endwhile; else: ?>
<div class="empty-state"><i class="bi bi-people"></i><h3>No Students Found</h3><p>You currently have no students enrolled.</p></div>
<?php endif; ?>
</div>

<script>
const displayToggle=document.getElementById('displayToggle');
let absenceMode=true;
displayToggle.addEventListener('click',()=>{
absenceMode=!absenceMode;
displayToggle.classList.toggle('active');
document.querySelectorAll('.student-card').forEach(card=>{
const rates=JSON.parse(card.dataset.rates);
const id=card.querySelector('.rate-circle').id?.split('-')[1]??card.dataset.id;
const rateEl=document.getElementById('rate-'+id);
const labelEl=document.getElementById('label-'+id);
const progEl=card.querySelector('.rate-circle-progress');
const alertEl=document.getElementById('alert-'+id);
if(absenceMode){
rateEl.textContent=Math.round(rates.absence)+'%';
rateEl.className='rate-percentage '+(rates.absence>=25?'low':rates.absence>=15?'medium':'high');
labelEl.textContent='Absence Rate';
progEl.style.strokeDashoffset=263.89-(263.89*rates.absence/100);
progEl.className='rate-circle-progress absence';
if(alertEl) alertEl.style.display=rates.absence>=25?'flex':'none';
}else{
rateEl.textContent=Math.round(rates.presence)+'%';
rateEl.className='rate-percentage '+(rates.presence>=80?'high':rates.presence>=50?'medium':'low');
labelEl.textContent='Presence Rate';
progEl.style.strokeDashoffset=263.89-(263.89*rates.presence/100);
progEl.className='rate-circle-progress presence';
if(alertEl) alertEl.style.display='none';
}
});
});

function sendReminder(studentId,event){
const btn=event.target.closest('button');
const orig=btn.innerHTML;
btn.disabled=true;
fetch('',{
method:'POST',
headers:{'Content-Type':'application/x-www-form-urlencoded'},
body:'remind=1&student_id='+studentId
}).then(r=>r.json()).then(res=>{
if(res.ok && res.sent){
btn.innerHTML='<i class="bi bi-check2-circle"></i> Sent';
btn.style.background='linear-gradient(135deg,#00e676,#00b200)';
}else{
alert('Failed: '+res.reason+(res.msg?'\n'+res.msg:''));
btn.disabled=false;
btn.innerHTML=orig;
}
}).catch(err=>{
alert('AJAX Error: '+err);
btn.disabled=false;
btn.innerHTML=orig;
});
}
</script>

</body>
</html>
