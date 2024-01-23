<?php

require '../layouts/config.php';
global $link;

$id_Certificado = $_GET['id_Certificado'];

$sql = "DELETE FROM certificados WHERE id_Certificado = $id_Certificado";

//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-certificates.php");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();