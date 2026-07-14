<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\UnassignBathroomFromContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

require_once __DIR__ . '/../layouts/helpers.php';
require_authenticated_session('../auth-login.php');

global $link;
include('../layouts/config.php');

function bath_unassign_redirect(string $query): void {
    header('Location: ../dash-bathrooms.php' . ($query ? '?' . $query : ''));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_unassign_bath'])) {
    bath_unassign_redirect('flash=error&msg=' . urlencode('Petición inválida.'));
}

$idBath = (int) ($_POST['id_Bath'] ?? 0);
if ($idBath <= 0) {
    bath_unassign_redirect('flash=error&msg=' . urlencode('ID de baño inválido.'));
}

$csrf_token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : null;
if (!verify_csrf_token($csrf_token)) {
    bath_unassign_redirect('flash=error&msg=' . urlencode('La sesión del formulario expiró. Intente nuevamente.'));
}

$repo = new MysqliBathroomRepository($link);

$asignacion = $repo->findActiveAssignment($idBath);
if ($asignacion === null) {
    bath_unassign_redirect('flash=error&msg=' . urlencode('El baño no está asignado a ningún contrato activo.'));
}

try {
    (new UnassignBathroomFromContract($repo))->handle(
        $asignacion['id_Relacion'],
        $idBath,
        $asignacion['id_Contrato']
    );
} catch (\mysqli_sql_exception | \DomainException $e) {
    bath_unassign_redirect('flash=error&msg=' . urlencode('No se pudo retirar el baño. Intente nuevamente.'));
}

$link->close();

bath_unassign_redirect('flash=success&msg=' . urlencode('Baño retirado correctamente.'));
