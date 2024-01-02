<?php

require '../layouts/config.php';

$id_Cliente = $_GET['id_Cliente'];
$estado_Cliente = 0;

$sql = "UPDATE clientes SET estado_Cliente='$estado_Cliente' WHERE id_Cliente = '$id_Cliente'";

//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-customers.php");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();