<?php

require '../layouts/config.php';
global $link;

    $id_Contrato = $_GET['id_Contrato'];
    $estado_Contrato = 1;

// Actualizar el estado del contrato en la tabla "contratos"
$sqlContrato = "UPDATE contratos SET estado_Contrato = '$estado_Contrato' WHERE id_Contrato = '$id_Contrato'";

if ($link->query($sqlContrato) === TRUE) {
    // Obtener los baños asignados a este contrato
    $sqlBathrooms = "SELECT id_Bath FROM contrato_bathroom WHERE id_Contrato = '$id_Contrato'";
    $resultBathrooms = $link->query($sqlBathrooms);

    if ($resultBathrooms->num_rows > 0) {
        // Recorrer los baños y actualizar asignado_Bath a 0
        while ($row = $resultBathrooms->fetch_assoc()) {
            $id_Bath = $row['id_Bath'];
            $sqlUpdateBath = "UPDATE bathrooms SET asignado_Bath = 0 WHERE id_Bath = '$id_Bath'";
            $link->query($sqlUpdateBath);

            // Eliminar la relación de la tabla contrato_bathroom
            $sqlDeleteRelation = "DELETE FROM contrato_bathroom WHERE id_Contrato = '$id_Contrato' AND id_Bath = '$id_Bath'";
            $link->query($sqlDeleteRelation);
        }
    }

    // Redirigir a la página después de la actualización
    header("Location: ../dash-contracts.php");
} else {
    header("Location: ../dash-contracts.php");
}

// Cerrar la conexión
$link->close();
