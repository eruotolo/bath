<?php

global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $id_Cliente = $_POST['id_Cliente'];
    $id_Contrato = $_POST['id_Contrato'];
    $fechahoy_Certificado = date('Y-m-d');
    $fecha_Servicio = $_POST['fecha_Servicio'];

    // Obtener el último número correlativo de la base de datos
    $query = "SELECT MAX(SUBSTRING(nro_Certificado, 9)) AS ultimo_correlativo FROM Certificados";
    $result = mysqli_query($link, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $ultimoCorrelativo = $row['ultimo_correlativo'];

        // Incrementar el número correlativo
        $nuevoCorrelativo = $ultimoCorrelativo + 1;

        // Generar el número de certificado
        $nro_Certificado = date('dmY') . 'A' . $nuevoCorrelativo;

        // Insertar en la tabla Certificados
        $insertQuery = "INSERT INTO Certificados (nro_Certificado, id_Cliente, id_Contrato, fechahoy_Certificado, fecha_Servicio) VALUES ('$nro_Certificado', $id_Cliente, $id_Contrato, '$fechahoy_Certificado', '$fecha_Servicio')";

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
