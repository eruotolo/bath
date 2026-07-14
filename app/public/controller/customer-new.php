<?php

global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $rut_Cliente = $_POST['rut_Cliente'];
    $nombre_Cliente = $_POST['nombre_Cliente'];
    $telefono_Cliente = $_POST['telefono_Cliente'];
    $email_Cliente = $_POST['email_Cliente'];
    $direccion_Cliente = $_POST['direccion_Cliente'];
    $region_Cliente = $_POST['region_Cliente'];
    $ciudad_Cliente = $_POST['ciudad_Cliente'];
    $comuna_Cliente = $_POST['comuna_Cliente'];
    $estado_Cliente = 1;

    // Insertar en la tabla de clientes

    $sql = "INSERT INTO clientes (rut_Cliente, nombre_Cliente, direccion_Cliente, comuna_Cliente, ciudad_Cliente, region_Cliente, telefono_Cliente, email_Cliente, estado_Cliente) VALUE (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param(
        $stmt, "ssssssssi",
        $rut_Cliente, $nombre_Cliente, $direccion_Cliente, $comuna_Cliente,
        $ciudad_Cliente, $region_Cliente, $telefono_Cliente, $email_Cliente, $estado_Cliente
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: ../dash-customers.php');

}else{
    echo '<script>alert("No se pudo crear el cliente")</script>';
}
// Cerrar la conexión
$link->close();
