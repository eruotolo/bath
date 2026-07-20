<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Contract\CreateContract;
use App\Application\Bathroom\AssignBathroomToContract;
use App\Infrastructure\Persistence\MysqliContractRepository;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
include('../layouts/config.php');
require_once '../layouts/session.php';
require_once '../layouts/permissions.php';
require_once '../layouts/activity_logger.php';
require_permission('create', 'Contract');

function contractNewRedirect(string $query): void {
    header('Location: ../dash-contracts.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['crear'])) {
    contractNewRedirect('action=new&err=' . urlencode('Petición inválida.'));
}

$idCliente = (int) ($_POST['id_Cliente'] ?? 0);
$obra = trim((string) ($_POST['obra_Contrato'] ?? ''));
$direccion = trim((string) ($_POST['direccion_Contrato'] ?? ''));
$fechaInicio = trim((string) ($_POST['fechaInicio_Contrato'] ?? ''));
$fechaFin = trim((string) ($_POST['fechaFin_Contrato'] ?? ''));
$valorMensual = str_replace('.', '', trim((string) ($_POST['valorMensual_Contrato'] ?? '')));
$valorTotal = str_replace('.', '', trim((string) ($_POST['valorTotal_Contrato'] ?? '')));
$observacion = trim((string) ($_POST['observacion_Contrato'] ?? ''));
$idBanos = array_map('intval', (array) ($_POST['id_Bath'] ?? []));

$errores = [];

if ($idCliente <= 0) {
    $errores[] = 'Debe seleccionar un cliente.';
}
if ($obra === '') {
    $errores[] = 'El nombre de la obra es obligatorio.';
}
if ($direccion === '') {
    $errores[] = 'La dirección de la faena es obligatoria.';
}
if ($fechaInicio === '') {
    $errores[] = 'La fecha de inicio es obligatoria.';
}
if ($fechaFin === '') {
    $errores[] = 'La fecha de término es obligatoria.';
}
if ($valorMensual === '' || !is_numeric($valorMensual)) {
    $errores[] = 'El valor mensual es obligatorio y debe ser numérico.';
}
if ($valorTotal === '' || !is_numeric($valorTotal)) {
    $errores[] = 'El valor total estimado es obligatorio y debe ser numérico.';
}

if ($errores) {
    contractNewRedirect('action=new&err=' . urlencode(implode(' ', $errores)));
}

try {
    $useCase = new CreateContract(new MysqliContractRepository($link));
    $idContrato = $useCase->handle([
        'id_Cliente' => $idCliente,
        'obra_Contrato' => $obra,
        'direccion_Contrato' => $direccion,
        'estado_Contrato' => 2,
        'fechaInicio_Contrato' => $fechaInicio,
        'fechaFin_Contrato' => $fechaFin,
        'valorMensual_Contrato' => $valorMensual,
        'valorTotal_Contrato' => $valorTotal,
        'observacion_Contrato' => $observacion,
    ]);
} catch (\mysqli_sql_exception $e) {
    log_activity_ctx($link, 'CREATE', [
        'entidad' => 'Contract',
        'descripcion' => "Error al crear contrato para cliente id $idCliente (obra '$obra')",
        'datos' => $_POST,
        'resultado' => 'error',
    ]);
    contractNewRedirect('action=new&err=' . urlencode('No se pudo crear el contrato. Intente nuevamente.'));
}

$bathroomRepository = new MysqliBathroomRepository($link);
$bathAsignados = [];
foreach ($idBanos as $idBath) {
    if ($idBath <= 0) {
        continue;
    }
    try {
        (new AssignBathroomToContract($bathroomRepository))->handle($idContrato, $idBath);
        $bathAsignados[] = $idBath;
    } catch (\Throwable $e) {
        // Best-effort: un baño puntual puede fallar (p. ej. ya asignado por otro usuario)
        // sin abortar la creación del contrato ni el resto de asignaciones.
    }
}

log_activity_ctx($link, 'CREATE', [
    'entidad' => 'Contract',
    'entidad_id' => $idContrato,
    'descripcion' => "Creó contrato id $idContrato (cliente id $idCliente, obra '$obra')",
    'datos' => $_POST,
]);

contractNewRedirect('flash=success&msg=' . urlencode('Contrato creado correctamente.'));
