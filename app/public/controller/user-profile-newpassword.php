<?php

global $link;
include "../layouts/config.php";

if (isset($_POST['update'])){
    $id = $_POST['id'];
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "UPDATE users SET password='$hash' WHERE id = $id";

    //echo $query;
    //die();

    $result = mysqli_query($link, $query);

    //echo '<script> alert ("Actualizado")</script>';

    header('Location:../logout.php');
}else{
    echo '<script> alert ("No se pudo actualizar el password")</script>';
}