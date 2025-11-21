<?php
session_start();
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"]=="POST")
{
    $nom=$_POST['nom'];
    $prenom=$_POST['prenom'];
    $email=$_POST['email'];
    $password=$_POST['password'];

$hashedpassword=password_hash($password,PASSWORD_DEFAULT);

$sql="INSERT INTO users(nom,prenom,email,password,role) VALUES(?,?,?,?,'student')";

$stmt =$mysqli->prepare($sql);
$stmt->bind_param("ssss",$nom,$prenom,$email,$hashedpassword);

if($stmt->execute())
{
    echo "Inscription réussie";
}else
{
    echo "Erreur :" .$stmt->error;
}

}
?>
<form method="POST">
    Nom: <input type="text" name="nom" required><br>
    Prénom: <input type="text" name="prenom" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit" name="register">Register</button>
</form>