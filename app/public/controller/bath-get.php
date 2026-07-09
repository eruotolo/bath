<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\FindBathroom;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

include ('../layouts/config.php');
global $link;

$id_Bath = (int) $_POST['id_Bath'];

$useCase = new FindBathroom(new MysqliBathroomRepository($link));
$bathroom = $useCase->handle($id_Bath);

if ($bathroom !== null) {
    echo json_encode([
        'id_Bath' => $bathroom->id,
        'codigo_Bath' => $bathroom->codigoBath,
        'fechaCompra_Bath' => $bathroom->fechaCompraBath,
        'observacion_Bath' => $bathroom->observacionBath,
        'estado_Bath' => $bathroom->estadoBath,
    ]);
}else{
    echo "No se encontró datos para este baño";
}
