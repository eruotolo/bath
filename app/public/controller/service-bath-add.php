<?php

include ('../layouts/config.php');

global $link;

if (isset($_POST['update'])){
    $id_Servicio = $_POST['id_Servicio'];

    // Verifica si $_POST['id_Bath'] está definido y es un array
    if (isset($_POST['id_Bath']) && is_array($_POST['id_Bath'])) {
        // Itera sobre cada valor seleccionado y realiza la inserción en la base de datos
        foreach ($_POST['id_Bath'] as $id_Bath) {
            $query = "INSERT INTO servicios_bathrooms (id_Servicio, id_Bath) VALUES ('$id_Servicio', '$id_Bath')";

            if ($link->query($query) !== true){
                // Manejar el error aquí si es necesario
                echo "Error al insertar: " . $link->error;
            }
        }

        // Redirige después de realizar todas las inserciones
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
    } else {
        // Maneja el caso donde no se seleccionó ningún baño
        echo "No se seleccionaron baños";
    }
} else {
    // Maneja el caso donde no se activó el formulario
    header("../index.php");
}
?>
