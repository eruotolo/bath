<?php

global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $id_Cliente = $_POST['id_Cliente'];
    $id_Contrato = $_POST['id_Contrato'];
    $fecha_Servicio = $_POST['fecha_Servicio'];
    $fechahoy_Certificado = $_POST['fechahoy_Certificado'];


    // Obtener el último número correlativo de la base de datos
    $query = "SELECT MAX(nro_Certificado) AS ultimo_correlativo FROM certificados WHERE fechahoy_Certificado = $fechahoy_Certificado";

    //echo $query;
    //die();

    $result = mysqli_query($link, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $ultimoCorrelativo = $row['ultimo_correlativo'];

        // Incrementar el número correlativo
        $nuevoCorrelativo =  1 + intval($ultimoCorrelativo);

        // Convertir el nuevo correlativo a un string
        $nuevoCorrelativoStr = (string) $nuevoCorrelativo;

        // Insertar en la tabla Certificados
        $insertQuery = "INSERT INTO Certificados (nro_Certificado, id_Cliente, id_Contrato, fechahoy_Certificado, fecha_Servicio) 
                                VALUES ($nuevoCorrelativo, $id_Cliente, $id_Contrato, '$fechahoy_Certificado', '$fecha_Servicio')";

        //echo $insertQuery;
        //die();

        $result = mysqli_query($link, $insertQuery) or ($error = mysqli_error($link));

        header("Location: ../dash-certificates.php");
        exit();
    } else {
        // Manejar el error al obtener el número correlativo
        echo "Error al obtener el número correlativo";
    }
}