<?php
global $link;
include ('../layouts/config.php');

if(isset($_POST['crear'])) {
    $id_Cliente = $_POST['id_Cliente'];
    $obra_Contrato = $_POST['obra_Contrato'];
    $direccion_Contrato = $_POST['direccion_Contrato'];
    $estado_Contrato = $_POST['estado_Contrato'];
    $fechaInicio_Contrato = $_POST['fechaInicio_Contrato'];
    $fechaFin_Contrato = $_POST['fechaFin_Contrato'];
    $valorMensual_Contrato = $_POST['valorMensual_Contrato'];
    $valorTotal_Contrato = $_POST['valorTotal_Contrato'];
    $observacion_Contrato = $_POST['observacion_Contrato'];

    $sql = "INSERT INTO contratos (id_Cliente, obra_Contrato, direccion_Contrato, estado_Contrato, fechaInicio_Contrato, fechaFin_Contrato, valorMensual_Contrato, valorTotal_Contrato, observacion_Contrato) VALUE ('$id_Cliente', '$obra_Contrato', '$direccion_Contrato','$estado_Contrato', '$fechaFin_Contrato', '$fechaFin_Contrato', '$valorMensual_Contrato', '$valorTotal_Contrato', '$observacion_Contrato')";

    // echo $sql;
    // die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    $id_Contrato = mysqli_insert_id($link);

    //echo $error;
    //die();

    header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");

}else{
    echo '<script>alert("No se pudo crear el contrato")</script>';
}
// Cerrar la conexión
$link->close();

