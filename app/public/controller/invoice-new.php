<?php

include '../layouts/config.php';
global $link;

if(isset($_POST['crear'])){
    $id_Cliente = $_POST['id_Cliente'];
    $id_Contrato = $_POST['id_Contrato'];
    $numero_Factura = $_POST['numero_Factura'];
    $fecha_Factura = $_POST['fecha_Factura'];
    $valor_Factura = $_POST['valor_Factura'];
    $estado_Factura = 1;

    $sql = "INSERT INTO facturas (id_Cliente, id_Contrato, numero_Factura, fecha_Factura, valor_Factura, estado_Factura) VALUES ($id_Cliente, $id_Contrato, '$numero_Factura', '$fecha_Factura', $valor_Factura, $estado_Factura)";

    //echo $sql;
    //die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    // Obtener el ID de la factura creada
    $id_factura_creada = mysqli_insert_id($link);

    header("Location: ../dash-invoices-detail.php?id_Factura=$id_factura_creada");
}else{
    header("Location: ../index.php");
}
$link->close();
