<?php
global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $id_Contrato = $_POST['id_Contrato'];
    $id_Bath = $_POST['id_Bath'];
    $tipo_Servicio = $_POST['tipo_Servicio'];
    $fecha_Servicio = $_POST['fecha_Servicio'];
    $observaciones_Servicio = $_POST['observaciones_Servicio'];
    $estado_Servicio = $_POST['estado_Servicio'];

    $sql = "INSERT INTO servicios (id_Contrato, id_Bath, tipo_Servicio, fecha_Servicio, observaciones_Servicio, estado_Servicio) VALUE ('$id_Contrato', '$id_Bath', '$tipo_Servicio', '$fecha_Servicio', '$observaciones_Servicio', '$estado_Servicio')";

    //echo $sql;
    //die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    //echo $error;
    //die();

    header('Location: ../dash-services.php');

}else{
    echo '<script>alert("No se pudo crear el contrato")</script>';
}
// Cerrar la conexión
$link->close();