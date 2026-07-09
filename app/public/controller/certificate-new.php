<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Certificate\CreateCertificate;
use App\Infrastructure\Persistence\MysqliCertificateRepository;

global $link;
include('../layouts/config.php');

if (isset($_POST['crear'])) {
    $useCase = new CreateCertificate(new MysqliCertificateRepository($link));

    try {
        $useCase->handle($_POST);
        header('Location: ../dash-certificates.php');
        exit();
    } catch (\mysqli_sql_exception $e) {
        echo 'Error al insertar el registro: ' . $e->getMessage();
    }
}
