<?php
$pdo = new PDO("","","")

if(isset($_POST['']))
{
    $name = $_POST['module_name'];
    $prof = $_POST['professor_id'];

    $sql = $pdo-> prepare("INSERT INTO modules (module_name,professor_id) VALUE(?,?)");
    $sql->execute([$name ,$prof]);

    echo "Module added succefully!";

}
?>

<form method="POST">
    <input type="text" name="module_name" placeholder="Module name" required><br><br>

    <input type="number" name="professor_id" placeholder="Professor ID" required><br><br>

    <button name="add_module">Add Module</button>
</form>
