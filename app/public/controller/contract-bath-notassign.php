<?php
global $link;
require '../layouts/config.php';

$id_Contrato = (int) $_GET['id_Contrato'];
$id_Bath = (int) $_GET['id_Bath'];
$id_Relacion = (int) $_GET['id_Relacion'];

// ELIMINAR REGISTRO DE LA TABLA CONTRATO_BATHROOM

$stmt_delete = $link->prepare('DELETE FROM contrato_bathroom WHERE id_Relacion = ?');
$stmt_delete->bind_param('i', $id_Relacion);
$delete_ok = $stmt_delete->execute();
$stmt_delete->close();

$stmt_update = $link->prepare('UPDATE bathrooms SET asignado_Bath = 0 WHERE id_Bath = ?');
$stmt_update->bind_param('i', $id_Bath);
$update_ok = $stmt_update->execute();
$stmt_update->close();

if ($delete_ok && $update_ok) {
    // Cierre automático: si al desasignar este baño la obra se queda sin baños, pasa a "Terminado"
    $stmt_check = $link->prepare('SELECT COUNT(*) AS total FROM contrato_bathroom WHERE id_Contrato = ?');
    $stmt_check->bind_param('i', $id_Contrato);
    $stmt_check->execute();
    $sin_banos = $stmt_check->get_result()->fetch_assoc()['total'] == 0;
    $stmt_check->close();

    if ($sin_banos) {
        $stmt_terminar = $link->prepare('UPDATE contratos SET estado_Contrato = 1 WHERE id_Contrato = ?');
        $stmt_terminar->bind_param('i', $id_Contrato);
        $stmt_terminar->execute();
        $stmt_terminar->close();
    }

    header("Location: ../dash-contracts-item.php?id_Contrato=$id_Contrato");
} else {
    header("Location: ../index.php");
}

// Cerrar la conexión
$link->close();