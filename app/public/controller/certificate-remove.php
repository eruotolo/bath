<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\DeleteCertificate;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

require '../layouts/config.php';
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
global $link;

$id_Certificado = (int) $_GET['id_Certificado'];
require_permission('delete', 'Certificate', $id_Certificado);
$useCase = new DeleteCertificate(new MysqliCertificateRepository($link));

try {
    $useCase->handle($id_Certificado);
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Certificate',
        'entidad_id' => $id_Certificado,
        'descripcion' => "Eliminó certificado id $id_Certificado",
    ]);
    header('Location: ../dash-certificates.php?flash=success&msg=' . urlencode('Certificado eliminado correctamente'));
    exit();
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'DELETE', [
        'entidad' => 'Certificate',
        'entidad_id' => $id_Certificado,
        'descripcion' => "Error al eliminar certificado id $id_Certificado",
        'resultado' => 'error',
    ]);
    header('Location: ../dash-certificates.php?flash=error&msg=' . urlencode('No se pudo eliminar el certificado'));
    exit();
}

$link->close();
