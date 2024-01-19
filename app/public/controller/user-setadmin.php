<?php

global $link;
include ('../layouts/config.php');

if (isset($_GET['id_User'])){
    $id = $_GET['id_User'];
    $category = $_GET['category'];

    if($category == 1){
        $sql = "UPDATE users SET category = 2 WHERE id = $id";
    }else{
        $sql = "UPDATE users SET category = 1 WHERE id = $id";
    }

    //echo $sql;
    //die();

    if ($link->query($sql) === TRUE) {
        //echo '<script> alert ("Usuario dado de baja")</script>';
        header("Location: ../dash-users-list.php");
    } else {
        echo '<script> alert ("No se pudo setear como Admin")</script>';
    }

    // Cerrar la conexión
    $link->close();

}