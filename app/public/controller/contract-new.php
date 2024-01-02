<?php
global $link;
include ('../layouts/config.php');

if(isset($_POST['crear'])) {
    $id_Cliente = $_POST['id_Cliente'];
    $obra_Contrato = $_POST['obra_Contrato'];
    $estado_Contrato = $_POST['estado_Contrato'];
    $fechaInicio_Contrato = $_POST['fechaInicio_Contrato'];
    $fechaFin_Contrato = $_POST['fechaFin_Contrato'];
    $valorMensual_Contrato = $_POST['valorMensual_Contrato'];
    $valorTotal_Contrato = $_POST['valorTotal_Contrato'];

    $sql = "INSERT INTO contratos (id_Cliente, obra_Contrato, estado_Contrato, fechaInicio_Contrato, fechaFin_Contrato, valorMensual_Contrato, valorTotal_Contrato) VALUE ('$id_Cliente', '$obra_Contrato', '$estado_Contrato', '$fechaFin_Contrato', '$fechaFin_Contrato', '$valorMensual_Contrato', '$valorTotal_Contrato')";

    // echo $sql;
    // die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    //echo $error;
    //die();

    header('Location: ../dash-contracts.php');

}else{
    echo '<script>alert("No se pudo crear el contrato")</script>';
}
// Cerrar la conexión
$link->close();

