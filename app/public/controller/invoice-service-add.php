<?php

include '../layouts/config.php';
global $link;

if(isset($_POST['update'])){
    $id_Factura = $_POST['id_Factura'];
    $id_Servicio = $_POST['id_Servicio'];
    $id_Contrato = $_POST['id_Contrato'];

    $query = "INSERT INTO factura_servicio (id_Factura, id_Servicio) VALUES ($id_Factura, $id_Servicio)";

    //echo $query;
    //die();

    if ($link->query($query) === true){
        header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura&id_Contrato=$id_Contrato");
    }else{
        header("../index.php");
    }
}