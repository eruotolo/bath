<?php

require '../layouts/config.php';
global $link;

$id_Relacion = $_GET['id_Relacion'];
$id_Servicio = $_GET['id_Servicio'];

$sql = "DELETE FROM servicios_bathrooms WHERE id_Relacion = '$id_Relacion'";
//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();