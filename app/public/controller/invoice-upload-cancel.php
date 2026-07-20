<?php

session_start();
include '../layouts/config.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;
require_permission('create', 'Invoice');

if (isset($_SESSION['carga_facturas'])) {
    $filas_preview = is_array($_SESSION['carga_facturas']) ? count($_SESSION['carga_facturas']) : 0;
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => "Canceló la carga de facturas (descartó preview de $filas_preview filas)",
    ]);
} else {
    log_activity_ctx($link, 'IMPORT', [
        'entidad' => 'Invoice',
        'descripcion' => 'Canceló la carga de facturas (sin preview activo)',
    ]);
}

unset($_SESSION['carga_facturas']);

header('Location: ../dash-invoices-list.php');
