<?php
global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $nro_Servicio = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    $id_Contrato = $_POST['id_Contrato'];

    $instalacion_Tipo = isset($_POST['instalacion_Tipo']) ? $_POST['instalacion_Tipo'] : 0;
    $reparacion_Tipo = isset($_POST['reparacion_Tipo']) ? $_POST['reparacion_Tipo'] : 0;
    $limpieza_Tipo = isset($_POST['limpieza_Tipo']) ? $_POST['limpieza_Tipo'] : 0;
    $desinfeccion_Tipo = isset($_POST['desinfeccion_Tipo']) ? $_POST['desinfeccion_Tipo'] : 0;
    $sanitizacion_Tipo = isset($_POST['sanitizacion_Tipo']) ? $_POST['sanitizacion_Tipo'] : 0;
    $higienico_Tipo = isset($_POST['higienico_Tipo']) ? $_POST['higienico_Tipo'] : 0;
    $jabon_Tipo = isset($_POST['jabon_Tipo']) ? $_POST['jabon_Tipo'] : 0;
    $otros_Tipo = isset($_POST['otros_Tipo']) ? $_POST['otros_Tipo'] : 0;

    $fecha_Servicio = $_POST['fecha_Servicio'];
    $observaciones_Servicio = $_POST['observaciones_Servicio'];
    $estado_Servicio = 1;

    $servicio_query = "INSERT INTO servicios (id_Contrato, nro_Servicio, fecha_Servicio, observaciones_Servicio, estado_Servicio) VALUE ('$id_Contrato', '$nro_Servicio', '$fecha_Servicio', '$observaciones_Servicio', '$estado_Servicio')";


    $tipo_query = "INSERT INTO tipo_servicio (nro_Servicio, instalacion_Tipo, reparacion_Tipo, limpieza_Tipo, desinfeccion_Tipo, sanitizacion_Tipo, higienico_Tipo, jabon_Tipo, otros_Tipo) VALUE ('$nro_Servicio','$instalacion_Tipo', '$reparacion_Tipo', '$limpieza_Tipo', '$desinfeccion_Tipo', '$sanitizacion_Tipo', '$higienico_Tipo', '$jabon_Tipo', '$otros_Tipo')";

    //echo $servicio_query;
    //echo $tipo_query;
    //die();

    if ($link->query($servicio_query) === TRUE) {
        $id_Servicio = mysqli_insert_id($link); // Obtiene el ID del Servicio creado

        if ($link->query($tipo_query) === TRUE) {
            // REGISTRO EN SERVICIO Y TIPO DE SERVICIO CORRECTO;
            header("Location: ../dash-services-bath.php?id_Servicio=$id_Servicio");
        } else {
            // REGISTRO EN TIPO DE SERVICIO INCORRECTO;
            echo '<script>alert("No se pudo crear el tipo de servicio")</script>';
        }
    } else {
        // REGISTRO EN SERVICIO INCORRECTO;
        echo '<script>alert("No se pudo crear el servicio")</script>';
    }

}
// Cerrar la conexión
$link->close();
