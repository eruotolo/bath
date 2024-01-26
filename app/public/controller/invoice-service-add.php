<?php

include '../layouts/config.php';
global $link;

if(isset($_POST['update'])){
    $id_Factura = $_POST['id_Factura'];
    $id_Servicio = $_POST['id_Servicio'];

    $query = "INSERT INTO factura_servicio (id_Factura, id_Servicio) VALUES ($id_Factura, $id_Servicio)";

    //echo $query;
    //die();

    if ($link->query($query) === true){
        header("Location: ../dash-invoices-detail.php?id_Factura=$id_Factura");
    }else{
        header("../index.php");
    }
}