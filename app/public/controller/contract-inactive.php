<?php

require '../layouts/config.php';

$id_Contrato = $_GET['id_Contrato'];
$estado_Contrato = 1;

$sql = "UPDATE contratos SET estado_Contrato = '$estado_Contrato' WHERE id_Contrato = '$id_Contrato'";

 //echo $sql;
 //die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-contracts.php");
} else {
    header("Location: ../dash-contracts.php");
}

// Cerrar la conexión
$link->close();
