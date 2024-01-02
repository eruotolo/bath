<?php

global $link;

include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Contacto = $_POST['idC'];
    $id_Cliente = $_POST['idCC'];
    $nombre_Contacto = $_POST['nombreC'];
    $apellido_Contacto = $_POST['apellidoC'];
    $rut_Contacto = $_POST['rutC'];
    $telefono_Contacto = $_POST['telefonoC'];
    $direccion_Contacto = $_POST['direccionC'];

    $query = "UPDATE contactos SET 
                     id_Contacto = '$id_Contacto', 
                     id_Cliente = '$id_Cliente',
                     nombre_Contacto = '$nombre_Contacto',
                     apellido_Contacto = '$apellido_Contacto',
                     rut_Contacto = '$rut_Contacto',
                     telefono_Contacto = '$telefono_Contacto',
                     direccion_Contacto = '$direccion_Contacto'
                     WHERE id_Contacto = $id_Contacto";
    //echo $query;
    //die();

    $result = mysqli_query($link, $query) or ($error= mysqli_error($link));

    //echo $error;
    //die();

    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");
}else{
    echo '<script>alert("No se pudo actualizar el contacto)</script>';
}
// Cerrar la conexión
$link->close();