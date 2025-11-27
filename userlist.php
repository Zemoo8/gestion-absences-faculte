<?php
<?php
session_start();
require_once '../config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD']=='POST'){
    $module_name = $_POST['module_name'];
    $stmt = $mysqli->prepare("INSERT INTO modules(module_name) VALUES(?)");
    $stmt->bind_param("s",$module_name);
    $stmt->execute();
}
?>

<h2>Add Module</h2>
<form method="POST">
    Module Name: <input type="text" name="module_name" required><br>
    <button type="submit">Add Module</button>
</form>

?>