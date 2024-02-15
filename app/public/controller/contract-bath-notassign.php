<?php
global $link;
require '../layouts/config.php';

$id_Contrato = $_GET['id_Contrato'];
$id_Bath = $_GET['id_Bath'];
$id_Relacion = $_GET['id_Relacion'];

// ELIMINAR REGISTRO DE LA TABLA CONTRATO_BATHROOM

$update_query = "UPDATE bathrooms SET asignado_Bath = 0 WHERE id_Bath = $id_Bath";

$delete_query = "DELETE FROM contrato_bathroom WHERE id_Relacion = $id_Relacion";

//echo $delete_query;
//echo $update_query;
//die();

if ($link->query($delete_query) === TRUE && $link->query($update_query) === TRUE) {
//if ($link->query($update_query) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();