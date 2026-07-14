<?php

session_start();
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Cliente = intval($_POST['idCliente']);
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
                    rut_Cliente = ?,
                    nombre_Cliente = ?,
                    direccion_Cliente = ?,
                    comuna_Cliente = ?,
                    ciudad_Cliente = ?,
                    region_Cliente = ?,
                    telefono_Cliente = ?,
                    email_Cliente = ?,
                    estado_Cliente = ?
                    WHERE id_Cliente = ?";

    $stmt = mysqli_prepare($link, $query);
    mysqli_stmt_bind_param(
        $stmt, "ssssssssii",
        $rut_Cliente, $nombre_Cliente, $direccion_Cliente, $comuna_Cliente,
        $ciudad_Cliente, $region_Cliente, $telefono_Cliente, $email_Cliente,
        $estado_Cliente, $id_Cliente
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: ../dash-customers-item.php?id_Cliente=$id_Cliente");
}else{
    echo '<script>alert("No se pudo actualizar el contacto)</script>';
}
// Cerrar la conexión
$link->close();