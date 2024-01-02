<?php
session_start();
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $id_Cliente = $_POST['id_Cliente'];
    $nombre_Contacto = $_POST['nombre_Contacto'];
    $apellido_Contacto = $_POST['apellido_Contacto'];
    $rut_Contacto = $_POST['rut_Contacto'];
    $telefono_Contacto = $_POST['telefono_Contacto'];
    $direccion_Contacto = $_POST['direccion_Contacto'];
    $estado_Contacto = 1;

    $sql = "INSERT INTO contactos (id_Cliente, nombre_Contacto, apellido_Contacto, rut_Contacto, telefono_Contacto, direccion_Contacto, estado_Contacto) VALUE ('$id_Cliente', '$nombre_Contacto', '$apellido_Contacto', '$rut_Contacto', '$telefono_Contacto', '$direccion_Contacto', '$estado_Contacto')";

    //echo $sql;
    //die();

    $result = mysqli_query($link, $sql) or ($error = mysqli_error($link));

    //echo $error;
    //die();

    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");

}else{
    echo '<script>alert("No se pudo crear el cliente")</script>';
}
// Cerrar la conexión
$link->close();