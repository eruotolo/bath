<?php

require '../layouts/config.php';
global $link;

$id_Relacion = $_GET['id_Relacion'];
$id_Factura = $_GET['id_Factura'];

$sql = "DELETE FROM factura_servicio WHERE id_Relacion = $id_Relacion";
//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();