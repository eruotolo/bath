<?php

global $link;
include ('../layouts/config.php');

if (isset($_POST['crear'])){
    $id_Cliente = $_POST['id_Cliente'];
    $id_Contrato = $_POST['id_Contrato'];
    $fecha_Servicio = $_POST['fecha_Servicio'];
    $fechahoy_Certificado = $_POST['fechahoy_Certificado'];

    // Obtener el último número correlativo
    $query = "SELECT MAX(nro_Certificado) AS ultimo_correlativo FROM certificados WHERE fechahoy_Certificado = CURDATE()";

    //echo $query;
    //die();
    echo "antes del result";
    $result  = mysqli_query($link, $query) or ($error = mysqli_error());
    echo "despues del result";
    echo $error;
    die();

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $ultimoCorrelativo = $row['ultimo_correlativo'];

        // Si no hay datos, se asigna el valor inicial 1
        if ($ultimoCorrelativo === NULL) {
            $nuevoCorrelativo = 1;
        } else {
            // Incrementar el número correlativo
            $nuevoCorrelativo = 1 + intval($ultimoCorrelativo);
        }

        // Convertir el nuevo correlativo a string con ceros a la izquierda
        $nuevoCorrelativoStr = sprintf("%05d", $nuevoCorrelativo);

        // Insertar en la tabla Certificados
        $insertQuery = "INSERT INTO Certificados (nro_Certificado, id_Cliente, id_Contrato, fechahoy_Certificado, fecha_Servicio) 
                                VALUES ($nuevoCorrelativoStr, $id_Cliente, $id_Contrato, CURDATE(), '$fecha_Servicio')";

        $result = mysqli_query($link, $insertQuery) or ($error = mysqli_error($link));

        if ($result) {
            header("Location: ../dash-certificates.php");
            exit();
        } else {
            echo "Error al insertar el registro: " . $error;
        }
    } else {
        echo "Error al obtener el número correlativo";
    }
}

?>
