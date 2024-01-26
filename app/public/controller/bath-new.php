<?php

global $link;

include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $codigo_Bath = $_POST['codigo_Bath'];
    $fechaCompra_Bath = $_POST['fechaCompra_Bath'];
    $observacion_Bath = $_POST['observacion_Bath'];
    $estado_Bath = $_POST['estado_Bath'];

    $sql ="INSERT INTO bathrooms(codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath) 
    VALUE ('$codigo_Bath', '$fechaCompra_Bath', '$observacion_Bath', '$estado_Bath')";

    //echo $sql;
    //die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    //echo $error;
    //die();

    header('Location: ../dash-bathrooms.php');

}else{
    echo '<script>alert("No se pudo crear el cliente")</script>';
}
// Cerrar la conexión
$link->close();