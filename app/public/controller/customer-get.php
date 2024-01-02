<?php

session_start();
include ('../layouts/config.php');

$id_Cliente = $_POST['id_Cliente'];

$sql = "SELECT * FROM clientes WHERE id_Cliente = $id_Cliente";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result ) > 0) {
    $row = mysqli_fetch_array($result);

    $clienteData = array(
        'id_Cliente' => $row['id_Cliente'],
        'rut_Cliente' => $row['rut_Cliente'],
        'nombre_Cliente' => $row['nombre_Cliente'],
        'telefono_Cliente' => $row['telefono_Cliente'],
        'email_Cliente' => $row['email_Cliente'],
        'direccion_Cliente' => $row['direccion_Cliente'],
        'comuna_Cliente' => $row['comuna_Cliente'],
        'ciudad_Cliente' => $row['ciudad_Cliente'],
        'region_Cliente' =>  $row['region_Cliente'],
    );

    echo json_encode($clienteData);
}else {
    echo "No se encontraron datos para este cliente";
}