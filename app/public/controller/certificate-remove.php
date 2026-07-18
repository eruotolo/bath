<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\DeleteCertificate;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

require '../layouts/config.php';
global $link;

$id_Certificado = (int) $_GET['id_Certificado'];
$useCase = new DeleteCertificate(new MysqliCertificateRepository($link));

try {
    $useCase->handle($id_Certificado);
    header('Location: ../dash-certificates.php?flash=success&msg=' . urlencode('Certificado eliminado correctamente'));
    exit();
} catch (\mysqli_sql_exception $e) {
    header('Location: ../dash-certificates.php?flash=error&msg=' . urlencode('No se pudo eliminar el certificado'));
    exit();
}

$link->close();
