<?php


global $link;
require '../layouts/config.php';

$id_Bath = $_GET['id_Bath'];

// ELIMINAR REGISTRO DE LA TABLA CONTRATO_BATHROOM
//$delete_query = "DELETE FROM contrato_bathroom WHERE id_Relacion = $id_Relacion";
$update_query = "UPDATE bathrooms SET asignado_Bath = 0 WHERE id_Bath = $id_Bath";

//echo $delete_query;
//echo $update_query;
//die();

//if ($link->query($delete_query) === TRUE && $link->query($update_query) === TRUE) {
if ($link->query($update_query) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-bathrooms.php");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();
