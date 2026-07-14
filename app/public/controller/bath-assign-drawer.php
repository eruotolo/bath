<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\AssignBathroomToContract;
use App\Application\Bathroom\FindBathroom;
use App\Application\Contract\FindContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;
use App\Infrastructure\Persistence\MysqliContractRepository;

require_once __DIR__ . '/../layouts/helpers.php';
require_authenticated_session('../auth-login.php');

global $link;
include('../layouts/config.php');

function bath_assign_redirect(string $query): void {
    header('Location: ../dash-bathrooms.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_assign_bath'])) {
    bath_assign_redirect('flash=error&msg=' . urlencode('Petición inválida.'));
}

$idBath     = (int) ($_POST['id_Bath'] ?? 0);
$idContrato = (int) ($_POST['id_Contrato'] ?? 0);

if ($idBath <= 0 || $idContrato <= 0) {
    bath_assign_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('Datos de asignación inválidos.'));
}

$csrf_token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!verify_csrf_token($csrf_token)) {
    bath_assign_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('La sesión del formulario expiró. Intente nuevamente.'));
}

$bathRepo = new MysqliBathroomRepository($link);

$bath = (new FindBathroom($bathRepo))->handle($idBath);
if ($bath === null) {
    bath_assign_redirect('flash=error&msg=' . urlencode('Baño no encontrado.'));
}

if ($bath->estadoBath !== 1 || $bath->asignadoBath !== 0 || $bathRepo->findActiveAssignment($idBath) !== null) {
    bath_assign_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('El baño no está disponible para asignar.'));
}

$contractRepo = new MysqliContractRepository($link);

$contrato = (new FindContract($contractRepo))->handle($idContrato);
if ($contrato === null || $contrato->state !== 2) {
    bath_assign_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('El contrato seleccionado no está activo.'));
}

try {
    (new AssignBathroomToContract($bathRepo))->handle($idContrato, $idBath);
} catch (\mysqli_sql_exception | \DomainException $e) {
    bath_assign_redirect('action=edit&id=' . $idBath . '&err=' . urlencode('No se pudo asignar el baño. Intente nuevamente.'));
}

$link->close();

bath_assign_redirect('flash=success&msg=' . urlencode('Baño asignado correctamente.'));
