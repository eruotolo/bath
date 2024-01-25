<?php

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contrato = $_POST['id_Contrato'];
    $id_Bath = $_POST['id_Bath'];
    $asignado_Bath = 1;

    $update_query = "UPDATE bathrooms SET asignado_Bath = $asignado_Bath WHERE id_Bath = $id_Bath";
    $insert_query = "INSERT INTO contrato_bathroom (id_Contrato, id_Bath) VALUE('$id_Contrato', '$id_Bath')";

    //echo $update_query;
    //echo $insert_query;
    //die();

    if ($link->query($update_query) === true && $link->query($insert_query) === true){
        header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");
    }else {
        header("Location: ../index.php");
    }

    // Cerrar la conexión
    $link->close();

}
