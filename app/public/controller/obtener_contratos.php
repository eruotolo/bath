<?php

global $link;
include ('../layouts/config.php');

// Validar entrada
if (!isset($_POST['idCliente']) || !is_numeric($_POST['idCliente'])) {
    return;
}

$idCliente = intval($_POST['idCliente']);

// Comprobar si el cliente existe
$sqlCount = "SELECT COUNT(*) AS total FROM contratos WHERE id_Cliente = ?";
$stmtCount = mysqli_prepare($link, $sqlCount);

if (!$stmtCount) {
    die("Error al preparar la consulta: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmtCount, "i", $idCliente);
mysqli_stmt_execute($stmtCount);

$resultCount = mysqli_stmt_get_result($stmtCount);
$total = mysqli_fetch_assoc($resultCount)['total'];

mysqli_stmt_close($stmtCount);

if ($total == 0) {
    echo "No se encontraron contratos para el cliente especificado.";
    return;
}

// Consulta para obtener contratos asociados al id_Cliente
$sqlSelect = "SELECT * FROM contratos WHERE id_Cliente = ?";
$stmtSelect = mysqli_prepare($link, $sqlSelect);

if (!$stmtSelect) {
    die("Error al preparar la consulta: " . mysqli_error($link));
}

mysqli_stmt_bind_param($stmtSelect, "i", $idCliente);
mysqli_stmt_execute($stmtSelect);

$resultSelect = mysqli_stmt_get_result($stmtSelect);

// Construir opciones del select
$contratos = mysqli_fetch_all($resultSelect, MYSQLI_ASSOC);
foreach ($contratos as $contrato) {
    $idContrato = $contrato['id_Contrato'];
    $nombreContrato = $contrato['obra_Contrato'] ?? 'Nombre no disponible';
    echo '<option value="' . $idContrato . '">' . $nombreContrato . '</option>';
}


mysqli_stmt_close($stmtSelect);

