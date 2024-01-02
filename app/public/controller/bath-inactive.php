<?php

global $link;
require '../layouts/config.php';

$id_Bath = $_GET['id_Bath'];
$estado_Bath = 0;

$sql = "UPDATE bathrooms SET estado_Bath = '$estado_Bath' WHERE id_Bath = '$id_Bath'";

 //echo $sql;
 //die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-bathrooms.php");
}

// Cerrar la conexión
$link->close();
