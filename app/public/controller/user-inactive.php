<?php

global $link;
include ('../layouts/config.php');

if (isset($_GET['id_User'])){
    $id = $_GET['id_User'];
    $password = 'Guns026772';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $state = 0;

    $sql = "UPDATE users SET password = '$hash', state = '$state' WHERE id = '$id'";
    //echo $sql;
    //die();

    if ($link->query($sql) === TRUE) {
        //echo '<script> alert ("Usuario dado de baja")</script>';
        header("Location: ../dash-users-list.php");
    } else {
        echo '<script> alert ("No se pudo dar de baja al usuario")</script>';
    }

// Cerrar la conexión
    $link->close();

}
