<?php

include "../layouts/config.php";
global $link;

$id_Bath = $_GET['id_Bath'];

// Ejecutar la consulta
$sql = "DELETE FROM bathrooms WHERE id_Bath = $id_Bath";

//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    header('Location: ../dash-bathrooms.php?status=success&msg=' . urlencode('Baño eliminado correctamente'));
}else{
    header('Location: ../dash-bathrooms.php?status=error&msg=' . urlencode('No se pudo eliminar el baño'));
}
// Cerrar la conexión
$link->close();