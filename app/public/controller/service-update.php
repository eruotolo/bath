<?php
global $link;
include ('../layouts/config.php');

if (isset($_POST['update'])){
    $id_Servicio = $_POST['id_Servicio'];
    $nro_Servicio = $_POST['nro_Servicio'];
    $id_Contrato = $_POST['id_Contrato'];

    $instalacion_Tipo = isset($_POST['instalacion_Tipo']) ? 1 : 0;
    $reparacion_Tipo = isset($_POST['reparacion_Tipo']) ? 1 : 0;
    $limpieza_Tipo = isset($_POST['limpieza_Tipo']) ? 1 : 0;
    $desinfeccion_Tipo = isset($_POST['desinfeccion_Tipo']) ? 1 : 0;
    $sanitizacion_Tipo = isset($_POST['sanitizacion_Tipo']) ? 1 : 0;
    $higienico_Tipo = isset($_POST['higienico_Tipo']) ? 1 : 0;
    $jabon_Tipo = isset($_POST['jabon_Tipo']) ? 1 : 0;
    $otros_Tipo = isset($_POST['otros_Tipo']) ? 1 : 0;

    $fecha_Servicio = $_POST['fecha_Servicio'];
    $observaciones_Servicio = $_POST['observaciones_Servicio'];
    $estado_Servicio = 1;

    // Actualizar la tabla servicios
    $servicio_query = "UPDATE servicios SET
                        id_Contrato = '$id_Contrato',
                        nro_Servicio = '$nro_Servicio',
                        fecha_Servicio = '$fecha_Servicio',
                        observaciones_Servicio = '$observaciones_Servicio',
                        estado_Servicio = '$estado_Servicio'
                        WHERE id_Servicio = '$id_Servicio'";

    //echo $servicio_query;
    //die();

    // Actualizar la tabla tipo_servicio
    $tipo_query = "UPDATE tipo_servicio SET
                        instalacion_Tipo = '$instalacion_Tipo',
                        reparacion_Tipo = '$reparacion_Tipo',
                        limpieza_Tipo = '$limpieza_Tipo',
                        desinfeccion_Tipo = '$desinfeccion_Tipo',
                        sanitizacion_Tipo = '$sanitizacion_Tipo',
                        higienico_Tipo = '$higienico_Tipo',
                        jabon_Tipo = '$jabon_Tipo',
                        otros_Tipo = '$otros_Tipo'
                    WHERE nro_Servicio = '$nro_Servicio';";

    //echo $servicio_query;
    //echo $tipo_query;
    //die();

    if ($link->query($servicio_query) === TRUE && $link->query($tipo_query) === TRUE) {

        // ACTUALIZACIÓN CORRECTA
        header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
    } else {
        // ERROR EN LA ACTUALIZACIÓN
        echo '<script>alert("No se pudo actualizar el servicio: ' . $link->error . '")</script>';
    }

}
// Cerrar la conexión
$link->close();

