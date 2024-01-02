<?php

session_start();
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Cliente = $_POST['idCliente'];
    $rut_Cliente = $_POST['rutCliente'];
    $nombre_Cliente = $_POST['nombreCliente'];
    $direccion_Cliente = $_POST['direccionCliente'];
    $comuna_Cliente = $_POST['comunaCliente'];
    $ciudad_Cliente = $_POST['ciudadCliente'];
    $region_Cliente = $_POST['regionCliente'];
    $telefono_Cliente = $_POST['telefonoCliente'];
    $email_Cliente = $_POST['emailCliente'];
    $estado_Cliente = 1;

    $query = "UPDATE clientes SET 
                    id_Cliente = '$id_Cliente',
                    rut_Cliente = '$rut_Cliente',
                    nombre_Cliente = '$nombre_Cliente',
                    direccion_Cliente = '$direccion_Cliente',
                    comuna_Cliente = '$comuna_Cliente',
                    ciudad_Cliente = '$ciudad_Cliente',
                    region_Cliente = '$region_Cliente',
                    telefono_Cliente = '$telefono_Cliente',
                    email_Cliente = '$email_Cliente',
                    estado_Cliente = '$estado_Cliente'
                    WHERE id_Cliente = $id_Cliente";
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