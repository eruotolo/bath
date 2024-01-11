<?php

include ('../layouts/config.php');

$id_Contacto = $_POST['id_Contacto'];

$sql = "SELECT * FROM contactos WHERE id_Contacto = $id_Contacto";
$result = mysqli_query($link, $sql);
if (mysqli_num_rows($result ) > 0) {
    $row = mysqli_fetch_array($result);
    //echo "<h4>Nombre: ".$row['nombre_Contacto']."</h4>";
    $contactData = array(
        'id_Contacto' => $row['id_Contacto'],
        'id_Cliente' => $row['id_Cliente'],
        'nombre_Contacto' => $row['nombre_Contacto'],
        'apellido_Contacto' => $row['apellido_Contacto'],
        'rut_Contacto' => $row['rut_Contacto'],
        'telefono_Contacto' => $row['telefono_Contacto'],
        'direccion_Contacto' => $row['direccion_Contacto'],
        'observacion_Contacto' => $row['observacion_Contacto']
    );

    echo json_encode($contactData);

}else {
    echo "No se encontraron datos para este usuario";
}