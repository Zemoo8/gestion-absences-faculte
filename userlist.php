<?php
session_start();
require_once '../config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT id, nom, prenom, email, role FROM users";
$result = $mysqli->query($sql);
?>

<h2>User List</h2>
<table border="1">
    <tr>
        <th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Role</th>
    </tr>
    <?php while($row = $result->fetch_assoc()){ ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['nom'] ?></td>
        <td><?= $row['prenom'] ?></td>
        <td><?= $row['email'] ?></td>
        <td><?= $row['role'] ?></td>
    </tr>
    <?php } ?>
</table>
