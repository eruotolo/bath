<?php

global $link;
include ('../layouts/config.php');

// ELIMINAR PASS DEFAULT
if (isset($_GET['id_User'])){
    $id = $_GET['id_User'];
    $password = 'JuanSanchez_2024';
    $hash = password_hash($password, PASSWORD_DEFAULT);


    $sql = "UPDATE users SET password='$hash' WHERE id = $id";
    //echo $sql;
    //die();

    if ($link->query($sql) === TRUE) {
        //echo '<script> alert ("Usuario dado de baja")</script>';
        header("Location: ../dash-users-list.php");
    } else {
        echo '<script> alert ("No se pudo dar de baja al usuario")</script>';
        header('Location: ../index.php');
    }

// Cerrar la conexión
    $link->close();

}