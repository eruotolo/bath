<?php

include "../layouts/config.php";
global $link;

$id_Bath = $_GET['id_Bath'];

// Ejecutar la consulta
$sql = "DELETE FROM bathrooms WHERE id_Bath = $id_Bath";

//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    header("Location: ../dash-bathrooms.php");
}else{
    echo '<script>alert("No se pudo eliminar el baño)</script>';
}
// Cerrar la conexión
$link->close();