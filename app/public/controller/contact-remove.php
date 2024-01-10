<?php

global $link;
require '../layouts/config.php';

// Obtención del identificador único de la fila a eliminar
$id_Contacto = $_GET['id_Contacto'];
$id_Cliente = $_GET['id_Cliente'];

// Eliminar la fila de la tabla "Trazado"
$sql = "DELETE FROM contactos WHERE id_Contacto = '$id_Contacto'";
//echo $sql;
//die();

if ($link->query($sql) === TRUE) {
    //echo "Registro eliminado correctamente.";
    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();