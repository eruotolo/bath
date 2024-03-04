<?php

include "../layouts/config.php";
global $link;

$id_Factura = $_GET['id_Factura'];

// Ejecutar la consulta
$sql = "UPDATE facturas SET estado_Factura = 3 WHERE id_Factura = $id_Factura";

//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    header("Location: ../dash-invoices-list.php");
}else{
    echo '<script>alert("No se pudo eliminar el baño)</script>';
}
// Cerrar la conexión
$link->close();