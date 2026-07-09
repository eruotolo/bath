<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\SetBathroomEstado;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
require '../layouts/config.php';

$id_Bath = (int) $_GET['id_Bath'];

$useCase = new SetBathroomEstado(new MysqliBathroomRepository($link));
$useCase->handle($id_Bath, 1);

header("Location: ../dash-bathrooms.php");

// Cerrar la conexión
$link->close();
