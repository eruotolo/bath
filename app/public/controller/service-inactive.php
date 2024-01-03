<?php

require '../layouts/config.php';
global $link;

$id_Servicio = $_GET['id_Servicio'];
$estado_Servicio = 0;

$sql = "UPDATE servicios SET estado_Servicio = '$estado_Servicio' WHERE id_Servicio = '$id_Servicio'";

    //echo $sql;
    //die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-services.php");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();