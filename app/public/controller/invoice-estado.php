<?php

include ('../layouts/config.php');
global $link;


$id_Factura = $_GET['id_Factura'];
$estado_Factura = $_GET['estado_Factura'];

    if ($estado_Factura == 1){
        $sql = "UPDATE facturas SET estado_Factura = 1 WHERE id_Factura = $id_Factura";
    }elseif($estado_Factura == 2){
        $sql = "UPDATE facturas SET estado_Factura = 2 WHERE id_Factura = $id_Factura";
    }else{
        $sql = "UPDATE facturas SET estado_Factura = 3 WHERE id_Factura = $id_Factura";
    }

    //echo $sql;
    //die();

    $result = mysqli_query($link, $sql);

    //echo $error;
    //die();

if ($link->query($sql) === TRUE) {
    header("Location: ../dash-invoices-list.php");
}else{
    echo '<script>alert("No se pudo actualizar el contacto)</script>';
}
// Cerrar la conexión
$link->close();