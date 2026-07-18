<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\FindContract;
use App\Application\Invoice\ListUnbilledServicesForContract;
use App\Infrastructure\Persistence\MysqliContractRepository;
use App\Infrastructure\Persistence\MysqliInvoiceRepository;

global $link;
include ('../layouts/config.php');

if (!isset($_POST['idContrato']) || !is_numeric($_POST['idContrato'])) {
    return;
}

$idContrato = (int) $_POST['idContrato'];

$contrato = (new FindContract(new MysqliContractRepository($link)))->handle($idContrato);
if ($contrato === null) {
    return;
}

$servicios = (new ListUnbilledServicesForContract(new MysqliInvoiceRepository($link)))->handle($contrato->customerId, $idContrato);

foreach ($servicios as $servicio) {
    $idServicio = (int) $servicio['id_Servicio'];
    $valorServicio = (int) ($servicio['valor_Servicio'] ?? 0);
    $label = date('d/m/Y', strtotime($servicio['fecha_Servicio'])) . ' | N°: ' . (int) $servicio['nro_Servicio'];
    echo '<option value="' . $idServicio . '" data-valor="' . $valorServicio . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
}
