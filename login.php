<?php
session_start();
require_once 'config.php';

$error='';

if(isset($_POST['login'])){
    $email=$_POST['email'];
    $password=$_POST['password'];

    $sql="SELECT id, password, role FROM users WHERE email='$email'";
    $result=$mysqli->query($sql);

    if($result->num_rows==1){
        $row=$result->fetch_assoc();
        if(password_verify($password,$row['password'])){
            $_SESSION['user_id']=$row['id'];
            $_SESSION['role']=$row['role'];
            
            switch($row['role']){
                case 'admin':
                    header("Location: dashboard_admin.php"); break;
                case 'professor':
                    header("Location: dashboard_professor.php"); break;
                default:
                    header("Location: dashboard_student.php"); break;
            }
            exit();
        } else{
            $error="Invalid password";
        }
    } else{
        $error="User not found";
    }
}
?>

<h2>Login</h2>
<?php if($error) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="login">Login</button>
</form>
