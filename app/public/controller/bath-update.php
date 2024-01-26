<?php

include "../layouts/config.php";
global $link;

if (isset($_POST['update'])){
    $id_Bath = $_POST['idBath'];
    $codigo_Bath = $_POST['codigoBath'];
    $fechaCompra_Bath = $_POST['fechaCompraBath'];
    $observacion_Bath = $_POST['observacionBath'];
    $estado_Bath = $_POST['estadoBath'];

    $query = "UPDATE bathrooms SET
                    id_Bath = '$id_Bath',
                    codigo_Bath = '$codigo_Bath',
                    fechaCompra_Bath = '$fechaCompra_Bath',
                    observacion_Bath = '$observacion_Bath',
                    estado_Bath = '$estado_Bath'
                    WHERE id_Bath = $id_Bath";

    //echo $query;
    //die();

    $result = mysqli_query($link, $query) or ($error= mysqli_error($link));

    //echo $error;
    //die();

    header("Location: ../dash-bathrooms.php");
}else{
    echo '<script>alert("No se pudo actualizar el baño)</script>';
}
// Cerrar la conexión
$link->close();