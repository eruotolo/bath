<?php

include ('../layouts/config.php');

global $link;

if (isset($_POST['update'])){
    $id_Servicio = $_POST['id_Servicio'];
    $id_Bath = $_POST['id_Bath'];

    $query = "INSERT INTO servicios_bathrooms (id_Servicio, id_Bath) VALUES ('$id_Servicio', '$id_Bath')";

    //echo $query;
    //die();

    if ($link->query($query) === true){
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
    }else{
        header("../index.php");
    }
}
