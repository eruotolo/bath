<?php

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contrato = $_POST['id_Contrato'];
    $id_Cliente = $_POST['id_Cliente'];
    $obra_Contrato = $_POST['obra_Contrato'];
    $estado_Contrato = $_POST['estado_Contrato'];
    $fechaInicio_Contrato = $_POST['fechaInicio_Contrato'];
    $fechaFin_Contrato = $_POST['fechaFin_Contrato'];
    $valorMensual_Contrato = $_POST['valorMensual_Contrato'];
    $valorTotal_Contrato = $_POST['valorTotal_Contrato'];

    $query = "UPDATE contratos SET 
                     id_Contrato = '$id_Contrato',
                     id_Cliente = '$id_Cliente',
                     obra_Contrato = '$obra_Contrato',
                     estado_Contrato = '$estado_Contrato',
                     fechaInicio_Contrato = '$fechaInicio_Contrato',
                     fechaFin_Contrato = '$fechaFin_Contrato',
                     valorMensual_Contrato = '$valorMensual_Contrato',
                     valorTotal_Contrato = '$valorTotal_Contrato'
                     WHERE id_Contrato = '$id_Contrato'";

    // echo $query;
    // die();

    $result = mysqli_query($link, $query) or ($error = mysqli_error($link));

    // echo $error;
    // die();

    header("Location: ../dash-contracts-edit.php?id_Contrato=$id_Contrato");
}else{
    echo '<script>alert("No se pudo actualizar el contrato)</script>';
}
// Cerrar la conexión
$link->close();