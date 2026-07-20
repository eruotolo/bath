<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\CreateCertificate;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

global $link;
include('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
require_permission('create', 'Certificate');

if (isset($_POST['crear'])) {
    $useCase = new CreateCertificate(new MysqliCertificateRepository($link));

    try {
        $id = $useCase->handle($_POST);
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Certificate',
            'entidad_id' => $id,
            'descripcion' => "Creó certificado id $id",
            'datos' => $_POST,
        ]);
        header('Location: ../dash-certificates.php?flash=success&msg=' . urlencode('Certificado creado correctamente'));
        exit();
    } catch (\mysqli_sql_exception $e) {
        log_activity_ctx($link, 'CREATE', [
            'entidad' => 'Certificate',
            'descripcion' => 'Error al crear certificado',
            'datos' => $_POST,
            'resultado' => 'error',
        ]);
        header('Location: ../dash-certificates.php?flash=error&msg=' . urlencode('No se pudo crear el certificado'));
        exit();
    }
} else {
    header('Location: ../dash-certificates.php?flash=error&msg=' . urlencode('No se pudo crear el certificado'));
    exit();
}
